<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppraisalStatus;
use App\Enums\TargetType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReopenAppraisalRequest;
use App\Http\Requests\Admin\StoreAppraisalRequest;
use App\Http\Requests\Admin\UpdateAppraisalRequest;
use App\Models\Appraisal;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\AppraisalOpened;
use App\Notifications\AppraisalSubmittedForReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AppraisalController extends Controller
{
    /**
     * Statuses that make up the "in progress" bucket used by the dashboard cards.
     */
    private const IN_PROGRESS_STATUSES = ['pending_employee', 'pending_reviewer', 'pending_signoff'];

    public function index(Request $request): View
    {
        $appraisals = $this->filteredAppraisals($request)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $years = $this->availableYears();

        $statusCounts = $this->filteredAppraisals($request, includeStatus: false)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.appraisals.index', compact('appraisals', 'years', 'statusCounts'));
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $appraisals = $this->filteredAppraisals($request)->orderBy('id')->get();

        $filename = 'appraisals-export-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($appraisals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Position', 'Reviewer', 'Year', 'Status', 'Completion Mode', 'Overall Rating', 'Appraisal Date', 'Employee Signed', 'Reviewer Signed']);

            foreach ($appraisals as $appraisal) {
                fputcsv($out, [
                    $appraisal->employee->name,
                    $appraisal->employee->position,
                    $appraisal->reviewer->name,
                    $appraisal->year,
                    $appraisal->status->label(),
                    $appraisal->completion_mode->label(),
                    $appraisal->overall_rating?->value,
                    optional($appraisal->appraisal_date)->format('Y-m-d'),
                    optional($appraisal->employee_signed_at)->format('Y-m-d H:i'),
                    optional($appraisal->reviewer_signed_at)->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filteredAppraisals(Request $request, bool $includeStatus = true): Builder
    {
        return Appraisal::query()
            ->when($includeStatus, fn ($q) => $q->with(['employee', 'reviewer']))
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->when($includeStatus && $request->filled('status'), function ($q) use ($request) {
                $status = (string) $request->string('status');

                $status === 'in_progress'
                    ? $q->whereIn('status', self::IN_PROGRESS_STATUSES)
                    : $q->where('status', $status);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->whereHas('employee', fn ($q2) => $q2->where('name', 'like', $term));
            })
            ->when($request->filled('completion_mode'), fn ($q) => $q->where('completion_mode', $request->string('completion_mode')));
    }

    public function create(Request $request): View
    {
        $years = $this->availableYears();
        $users = $request->user()->appraisableStaff()->orderBy('name')->get();
        $selectedYear = $request->integer('year') ?: $years->first();

        return view('admin.appraisals.create', compact('years', 'users', 'selectedYear'));
    }

    public function store(StoreAppraisalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $openNow = ($data['intent'] ?? 'draft') === 'open';

        $appraisal = DB::transaction(function () use ($data, $openNow) {
            $appraisal = Appraisal::create([
                'user_id' => $data['user_id'],
                'reviewer_id' => $data['reviewer_id'],
                'year' => $data['year'],
                'appraisal_date' => $data['appraisal_date'] ?? null,
                'status' => $openNow ? AppraisalStatus::PendingEmployee : AppraisalStatus::Draft,
            ]);

            $this->syncCurrentTargets($appraisal, $data['targets'] ?? []);

            return $appraisal;
        });

        $appraisal->load('employee');

        if ($openNow) {
            $appraisal->employee->notify(new AppraisalOpened($appraisal));
            AuditLog::record('appraisal.created', $appraisal, sprintf(
                'Created and opened appraisal for %s (%s) — awaiting employee%s',
                $appraisal->employee->name, $appraisal->year, $this->creatorNote($request),
            ));

            return redirect()->route($this->indexRoute($request))
                ->with('status', 'Appraisal created and opened for the employee.');
        }

        AuditLog::record('appraisal.created', $appraisal, sprintf(
            'Created draft appraisal for %s (%s)%s',
            $appraisal->employee->name, $appraisal->year, $this->creatorNote($request),
        ));

        return redirect()->route($this->indexRoute($request))->with('status', 'Appraisal created as draft.');
    }

    public function edit(Appraisal $appraisal): View
    {
        $this->authorize('update', $appraisal);

        $appraisal->load(['employee', 'reviewer', 'currentTargets']);
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('admin.appraisals.edit', compact('appraisal', 'users'));
    }

    public function update(UpdateAppraisalRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($appraisal, $data) {
            $appraisal->update([
                'user_id' => $data['user_id'],
                'reviewer_id' => $data['reviewer_id'],
                'appraisal_date' => $data['appraisal_date'] ?? null,
            ]);

            $this->syncCurrentTargets($appraisal, $data['targets'] ?? [], preserveResponses: true);
        });

        $appraisal->load('employee');

        AuditLog::record('appraisal.updated', $appraisal, sprintf(
            'Edited appraisal for %s (%s)',
            $appraisal->employee->name, $appraisal->year,
        ));

        return redirect()->route('admin.appraisals.index')->with('status', 'Appraisal updated.');
    }

    public function destroy(Appraisal $appraisal): RedirectResponse
    {
        $this->authorize('delete', $appraisal);

        $appraisal->loadMissing('employee');
        $summary = sprintf(
            'Deleted appraisal for %s (%s), status %s',
            $appraisal->employee->name,
            $appraisal->year,
            $appraisal->status->label(),
        );

        $appraisal->delete();

        AuditLog::record('appraisal.deleted', $appraisal, $summary);

        return redirect()->route('admin.appraisals.index')->with('status', 'Appraisal deleted.');
    }

    public function open(Request $request, Appraisal $appraisal): RedirectResponse
    {
        $this->authorize('open', $appraisal);

        if ($appraisal->status === AppraisalStatus::Draft) {
            $appraisal->update(['status' => AppraisalStatus::PendingEmployee]);
            $appraisal->employee->notify(new AppraisalOpened($appraisal));

            AuditLog::record('appraisal.opened', $appraisal, sprintf(
                'Opened appraisal for %s — now awaiting employee%s', $appraisal->employee->name, $this->creatorNote($request),
            ));
        }

        return redirect()->back()->with('status', 'Appraisal opened for employee.');
    }

    public function showReopen(Appraisal $appraisal): View
    {
        $this->authorize('reopen', $appraisal);

        $appraisal->load(['employee', 'reviewer']);

        return view('admin.appraisals.reopen', compact('appraisal'));
    }

    public function reopen(ReopenAppraisalRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $data = $request->validated();
        $fromStatus = $appraisal->status;
        $toStatus = AppraisalStatus::from($data['target_status']);
        $hadSignature = $appraisal->employee_signed_at !== null || $appraisal->reviewer_signed_at !== null;

        $appraisal->update([
            'status' => $toStatus,
            'employee_signed_at' => null,
            'reviewer_signed_at' => null,
        ]);

        $appraisal->load(['employee', 'reviewer']);

        if ($toStatus === AppraisalStatus::PendingEmployee) {
            $appraisal->employee->notify(new AppraisalOpened($appraisal));
        } else {
            $appraisal->reviewer->notify(new AppraisalSubmittedForReview($appraisal));
        }

        AuditLog::record('appraisal.reopened', $appraisal, sprintf(
            'Sent back from %s to %s by %s — reason: %s%s',
            $fromStatus->label(), $toStatus->label(), $request->user()->name, $data['reason'],
            $hadSignature ? ' (existing signature(s) cleared)' : '',
        ));

        return redirect()->route('admin.appraisals.index')
            ->with('status', "Appraisal sent back to {$toStatus->label()}.");
    }

    /**
     * Years selectable for a new/filtered appraisal: any year already in use,
     * plus a sensible range around today so both recent and further-ahead
     * years are always pickable without needing an appraisal to exist yet.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function indexRoute(Request $request): string
    {
        return $request->user()->isAdmin() ? 'admin.appraisals.index' : 'appraisals.index';
    }

    private function creatorNote(Request $request): string
    {
        $user = $request->user();

        return sprintf(' — by %s (%s)', $user->name, $user->role->value);
    }

    private function availableYears(): \Illuminate\Support\Collection
    {
        $currentYear = now()->year;
        $usedYears = Appraisal::query()->distinct()->pluck('year');
        $range = range($currentYear - 2, $currentYear + 5);

        return $usedYears->merge($range)
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * Replace an appraisal's Section 1 (current) targets from form input.
     * When $preserveResponses is set, any employee-entered target_met / comments
     * are carried over onto the matching target row (matched by hidden id).
     *
     * @param  array<int, array<string, mixed>>  $targets
     */
    private function syncCurrentTargets(Appraisal $appraisal, array $targets, bool $preserveResponses = false): void
    {
        $existing = $preserveResponses
            ? $appraisal->targets()->where('target_type', TargetType::Current)->get()->keyBy('id')
            : collect();

        $appraisal->targets()->where('target_type', TargetType::Current)->delete();

        $number = 1;
        foreach ($targets as $input) {
            $isBlank = blank($input['target_text'] ?? null)
                && blank($input['action_text'] ?? null)
                && blank($input['success_criteria'] ?? null);

            if ($isBlank) {
                continue;
            }

            $previous = ! empty($input['id']) ? $existing->get((int) $input['id']) : null;

            $appraisal->targets()->create([
                'target_type' => TargetType::Current,
                'target_number' => $number++,
                'target_text' => $input['target_text'] ?? null,
                'action_text' => $input['action_text'] ?? null,
                'success_criteria' => $input['success_criteria'] ?? null,
                'target_met' => $previous?->target_met,
                'comments' => $previous?->comments,
            ]);
        }
    }

    /**
     * Fetch the employee's most recent prior next-year targets (if any) to prefill
     * this appraisal's current-target text fields, mirroring the paper form's carry-forward.
     */
    public function priorTargets(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        abort_unless($request->user()->appraisableStaff()->whereKey($user->id)->exists(), 403);

        $prior = Appraisal::where('user_id', $user->id)
            ->whereHas('targets', fn ($q) => $q->where('target_type', TargetType::NextYear))
            ->with(['targets' => fn ($q) => $q->where('target_type', TargetType::NextYear)->orderBy('target_number')])
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'targets' => $prior?->targets->map(fn ($t) => [
                'target_text' => $t->target_text,
                'action_text' => $t->action_text,
                'success_criteria' => $t->success_criteria,
            ])->values() ?? [],
        ]);
    }
}

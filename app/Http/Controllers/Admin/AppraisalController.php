<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppraisalStatus;
use App\Enums\TargetType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAppraisalRequest;
use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\User;
use App\Notifications\AppraisalOpened;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class AppraisalController extends Controller
{
    public function index(Request $request): View
    {
        $appraisals = $this->filteredAppraisals($request)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $cycles = AppraisalCycle::orderByDesc('start_date')->get();

        $statusCounts = $this->filteredAppraisals($request, includeStatus: false)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.appraisals.index', compact('appraisals', 'cycles', 'statusCounts'));
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $appraisals = $this->filteredAppraisals($request)->orderBy('id')->get();

        $filename = 'appraisals-export-'.now()->format('Y-m-d').'.csv';

        return Response::streamDownload(function () use ($appraisals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee', 'Position', 'Reviewer', 'Cycle', 'Term', 'Status', 'Overall Rating', 'Appraisal Date', 'Employee Signed', 'Reviewer Signed']);

            foreach ($appraisals as $appraisal) {
                fputcsv($out, [
                    $appraisal->employee->name,
                    $appraisal->employee->position,
                    $appraisal->reviewer->name,
                    $appraisal->cycle->name,
                    $appraisal->cycle->term->value,
                    $appraisal->status->label(),
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
            ->when($includeStatus, fn ($q) => $q->with(['employee', 'reviewer', 'cycle']))
            ->when($request->filled('cycle_id'), fn ($q) => $q->where('cycle_id', $request->integer('cycle_id')))
            ->when($includeStatus && $request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
    }

    public function create(Request $request): View
    {
        $cycles = AppraisalCycle::orderByDesc('start_date')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $selectedCycleId = $request->integer('cycle_id') ?: $cycles->first()?->id;

        return view('admin.appraisals.create', compact('cycles', 'users', 'selectedCycleId'));
    }

    public function store(StoreAppraisalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $appraisal = Appraisal::create([
            'user_id' => $data['user_id'],
            'reviewer_id' => $data['reviewer_id'],
            'cycle_id' => $data['cycle_id'],
            'appraisal_date' => $data['appraisal_date'] ?? null,
            'status' => AppraisalStatus::Draft,
        ]);

        $number = 1;
        foreach (($data['targets'] ?? []) as $text) {
            if (blank($text)) {
                continue;
            }

            $appraisal->targets()->create([
                'target_type' => TargetType::Current,
                'target_number' => $number++,
                'target_text' => $text,
            ]);
        }

        return redirect()->route('admin.appraisals.index')->with('status', 'Appraisal created as draft.');
    }

    public function open(Appraisal $appraisal): RedirectResponse
    {
        $this->authorize('update', $appraisal);

        if ($appraisal->status === AppraisalStatus::Draft) {
            $appraisal->update(['status' => AppraisalStatus::PendingEmployee]);
            $appraisal->employee->notify(new AppraisalOpened($appraisal));
        }

        return redirect()->back()->with('status', 'Appraisal opened for employee.');
    }

    /**
     * Fetch the employee's most recent prior next-year targets (if any) to prefill
     * this cycle's current-target text fields, mirroring the paper form's carry-forward.
     */
    public function priorTargets(User $user): \Illuminate\Http\JsonResponse
    {
        $prior = Appraisal::where('user_id', $user->id)
            ->whereHas('targets', fn ($q) => $q->where('target_type', TargetType::NextYear))
            ->with(['targets' => fn ($q) => $q->where('target_type', TargetType::NextYear)->orderBy('target_number')])
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'targets' => $prior?->targets->pluck('target_text', 'target_number') ?? [],
        ]);
    }
}

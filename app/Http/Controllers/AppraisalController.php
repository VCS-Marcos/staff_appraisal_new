<?php

namespace App\Http\Controllers;

use App\Enums\AppraisalStatus;
use App\Enums\CompletionMode;
use App\Enums\PdNature;
use App\Enums\TargetType;
use App\Http\Requests\SignAppraisalRequest;
use App\Http\Requests\SignInPersonRequest;
use App\Http\Requests\UpdateAppraisalEmployeeRequest;
use App\Http\Requests\UpdateAppraisalReviewerRequest;
use App\Models\Appraisal;
use App\Models\AppraisalTarget;
use App\Models\AuditLog;
use App\Notifications\AppraisalReadyForSignoff;
use App\Notifications\AppraisalSubmittedForReview;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppraisalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $myAppraisals = Appraisal::with('cycle')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $teamAppraisals = $user->isReviewer() || $user->isAdmin()
            ? Appraisal::with(['employee', 'cycle'])
                ->where('reviewer_id', $user->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('appraisals.index', compact('myAppraisals', 'teamAppraisals'));
    }

    public function show(Appraisal $appraisal): View
    {
        $this->authorize('view', $appraisal);

        $appraisal->load(['employee.lineManager', 'reviewer', 'cycle', 'currentTargets', 'nextYearTargets', 'professionalDevelopment']);

        return view('appraisals.show', compact('appraisal'));
    }

    public function edit(Appraisal $appraisal): View
    {
        $this->authorize('updateAsEmployee', $appraisal);

        $appraisal->load(['employee.lineManager', 'reviewer', 'cycle', 'currentTargets', 'professionalDevelopment']);

        $actingOnBehalf = auth()->id() !== $appraisal->user_id;

        return view('appraisals.edit', compact('appraisal', 'actingOnBehalf'));
    }

    public function update(UpdateAppraisalEmployeeRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $data = $request->validated();
        $actingAsEmployee = $request->user()->id === $appraisal->user_id;

        $appraisal->update([
            'self_reflection' => $data['self_reflection'] ?? $appraisal->self_reflection,
            'completion_mode' => $actingAsEmployee ? $appraisal->completion_mode : CompletionMode::Assisted,
        ]);

        foreach ($data['targets'] ?? [] as $targetInput) {
            AppraisalTarget::where('id', $targetInput['id'])
                ->where('appraisal_id', $appraisal->id)
                ->update([
                    'target_met' => $targetInput['target_met'] ?? null,
                    'comments' => $targetInput['comments'] ?? null,
                ]);
        }

        $appraisal->professionalDevelopment()->delete();

        foreach (($data['pd'] ?? []) as $pdInput) {
            if (blank($pdInput['activity_name'] ?? null)) {
                continue;
            }

            $appraisal->professionalDevelopment()->create([
                'user_id' => $appraisal->user_id,
                'activity_name' => $pdInput['activity_name'],
                'nature' => $pdInput['nature'] ?? PdNature::Online,
                'provider' => $pdInput['provider'] ?? null,
                'impact_on_practice' => $pdInput['impact_on_practice'] ?? null,
                'hours' => $pdInput['hours'] ?? 0,
                'activity_date' => $pdInput['activity_date'] ?? null,
            ]);
        }

        if ($request->input('intent') === 'submit') {
            $appraisal->update(['status' => AppraisalStatus::PendingReviewer]);
            $appraisal->reviewer->notify(new AppraisalSubmittedForReview($appraisal));

            if ($actingAsEmployee) {
                AuditLog::record('appraisal.submitted_by_employee', $appraisal, 'Employee submitted their section — now awaiting reviewer');
            } else {
                AuditLog::record('appraisal.submitted_on_behalf', $appraisal,
                    "Employee's section captured in person by {$request->user()->name} on behalf of {$appraisal->employee->name} — now awaiting reviewer");
            }

            return redirect()->route('appraisals.index')->with('status',
                $actingAsEmployee ? 'Appraisal submitted to your reviewer.' : "Appraisal submitted on {$appraisal->employee->name}'s behalf.");
        }

        return redirect()->route('appraisals.edit', $appraisal)->with('status', 'Progress saved.');
    }

    public function review(Appraisal $appraisal): View
    {
        $this->authorize('updateAsReviewer', $appraisal);

        $appraisal->load(['employee.lineManager', 'reviewer', 'cycle', 'currentTargets', 'nextYearTargets', 'professionalDevelopment']);

        return view('appraisals.review', compact('appraisal'));
    }

    public function updateReview(UpdateAppraisalReviewerRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $data = $request->validated();

        $appraisal->update([
            'appraisal_date' => $data['appraisal_date'] ?? $appraisal->appraisal_date,
            'reviewer_comments' => $data['reviewer_comments'] ?? $appraisal->reviewer_comments,
            'overall_rating' => $data['overall_rating'] ?? $appraisal->overall_rating,
            'next_review_date' => $data['next_review_date'] ?? $appraisal->next_review_date,
        ]);

        $appraisal->targets()->where('target_type', TargetType::NextYear)->delete();

        $number = 1;
        foreach (($data['next_year_targets'] ?? []) as $targetInput) {
            $isBlank = blank($targetInput['target_text'] ?? null)
                && blank($targetInput['action_text'] ?? null)
                && blank($targetInput['success_criteria'] ?? null);

            if ($isBlank) {
                continue;
            }

            $appraisal->targets()->create([
                'target_type' => TargetType::NextYear,
                'target_number' => $number++,
                'target_text' => $targetInput['target_text'] ?? null,
                'action_text' => $targetInput['action_text'] ?? null,
                'success_criteria' => $targetInput['success_criteria'] ?? null,
            ]);
        }

        if ($request->input('intent') === 'submit') {
            $appraisal->update(['status' => AppraisalStatus::PendingSignoff]);
            $appraisal->employee->notify(new AppraisalReadyForSignoff($appraisal));
            $appraisal->reviewer->notify(new AppraisalReadyForSignoff($appraisal));

            AuditLog::record('appraisal.submitted_by_reviewer', $appraisal, 'Reviewer submitted the review — now awaiting sign-off');

            return redirect()->route('appraisals.index')->with('status', 'Review submitted, awaiting sign-off.');
        }

        return redirect()->route('appraisals.review', $appraisal)->with('status', 'Progress saved.');
    }

    public function signOff(Appraisal $appraisal): View
    {
        $this->authorize('sign', $appraisal);

        $appraisal->load(['employee.lineManager', 'reviewer', 'cycle', 'currentTargets', 'nextYearTargets', 'professionalDevelopment']);

        return view('appraisals.sign', compact('appraisal'));
    }

    public function submitSignOff(SignAppraisalRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $isEmployee = $appraisal->user_id === $request->user()->id;

        $appraisal->update([
            $isEmployee ? 'employee_signed_at' : 'reviewer_signed_at' => now(),
        ]);

        AuditLog::record('appraisal.signed', $appraisal,
            ($isEmployee ? 'Employee' : 'Reviewer').' signed the appraisal');

        $appraisal->refresh();

        if ($appraisal->isFullySigned()) {
            $appraisal->update(['status' => AppraisalStatus::Completed]);
            AuditLog::record('appraisal.completed', $appraisal, 'Appraisal fully signed and marked completed');
        }

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Signed successfully.');
    }

    public function signInPerson(Appraisal $appraisal): View
    {
        $this->authorize('signOnBehalf', $appraisal);

        $appraisal->load(['employee.lineManager', 'reviewer', 'cycle', 'currentTargets', 'nextYearTargets', 'professionalDevelopment']);

        return view('appraisals.sign-in-person', compact('appraisal'));
    }

    public function submitSignInPerson(SignInPersonRequest $request, Appraisal $appraisal): RedirectResponse
    {
        $actor = $request->user();
        $capturingEmployee = $appraisal->employee_signed_at === null;
        $capturingReviewer = $appraisal->reviewer_signed_at === null;

        DB::transaction(function () use ($appraisal, $capturingEmployee, $capturingReviewer) {
            if ($capturingEmployee) {
                $appraisal->employee_signed_at = now();
            }

            if ($capturingReviewer) {
                $appraisal->reviewer_signed_at = now();
            }

            $appraisal->completion_mode = CompletionMode::Assisted;
            $appraisal->save();
        });

        if ($capturingEmployee) {
            AuditLog::record('appraisal.signed_in_person', $appraisal,
                "Employee signature captured in person by {$actor->name} on behalf of {$appraisal->employee->name}");
        }

        if ($capturingReviewer) {
            $standingIn = $appraisal->reviewer_id !== $actor->id;

            AuditLog::record('appraisal.signed_in_person', $appraisal,
                "Reviewer signature captured in person by {$actor->name}"
                .($standingIn ? " (standing in for reviewer {$appraisal->reviewer->name})" : ''));
        }

        $appraisal->refresh();

        if ($appraisal->isFullySigned()) {
            $appraisal->update(['status' => AppraisalStatus::Completed]);
            AuditLog::record('appraisal.completed', $appraisal, 'Appraisal fully signed and marked completed (in-person session)');
        }

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Sign-off captured successfully.');
    }

    public function downloadPdf(Appraisal $appraisal): Response
    {
        $this->authorize('view', $appraisal);

        $appraisal->load(['employee', 'reviewer', 'cycle', 'currentTargets', 'nextYearTargets', 'professionalDevelopment']);

        AuditLog::record('appraisal.exported_pdf', $appraisal,
            "Exported PDF for {$appraisal->employee->name} ({$appraisal->cycle->name} {$appraisal->cycle->term->value})");

        $pdf = Pdf::loadView('appraisals.pdf', compact('appraisal'))->setPaper('a4');

        $filename = 'Appraisal-'.Str::slug($appraisal->employee->name).'-'.Str::slug($appraisal->cycle->name.'-'.$appraisal->cycle->term->value).'.pdf';

        return $pdf->download($filename);
    }
}

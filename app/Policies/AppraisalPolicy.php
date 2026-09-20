<?php

namespace App\Policies;

use App\Enums\AppraisalStatus;
use App\Models\Appraisal;
use App\Models\User;

class AppraisalPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Appraisal $appraisal): bool
    {
        return $user->isAdmin()
            || $appraisal->reviewer_id === $user->id
            || $appraisal->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isReviewer();
    }

    /**
     * Open a draft for the employee: admins any, reviewers only their own drafts.
     */
    public function open(User $user, Appraisal $appraisal): bool
    {
        if ($appraisal->status !== AppraisalStatus::Draft) {
            return false;
        }

        return $user->isAdmin()
            || ($user->isReviewer() && $appraisal->reviewer_id === $user->id);
    }

    public function update(User $user, Appraisal $appraisal): bool
    {
        return $user->isAdmin()
            || $appraisal->reviewer_id === $user->id
            || $appraisal->user_id === $user->id;
    }

    public function delete(User $user, Appraisal $appraisal): bool
    {
        return $user->isAdmin();
    }

    /**
     * Employee may fill in Section 1 target status/comments + Section 2 self-reflection
     * while the appraisal is still in an employee-editable stage. Their reviewer or an
     * admin may also use this same form on the employee's behalf during an in-person
     * session, for staff without portal access.
     */
    public function updateAsEmployee(User $user, Appraisal $appraisal): bool
    {
        if ($appraisal->status !== AppraisalStatus::PendingEmployee) {
            return false;
        }

        return $appraisal->user_id === $user->id
            || $appraisal->reviewer_id === $user->id
            || $user->isAdmin();
    }

    /**
     * Reviewer may fill in Section 2 reviewer comments/rating + Section 3 next-year targets
     * once the employee has submitted their side.
     */
    public function updateAsReviewer(User $user, Appraisal $appraisal): bool
    {
        return ($appraisal->reviewer_id === $user->id || $user->isAdmin())
            && $appraisal->status === AppraisalStatus::PendingReviewer;
    }

    public function sign(User $user, Appraisal $appraisal): bool
    {
        if ($appraisal->status !== AppraisalStatus::PendingSignoff) {
            return false;
        }

        if ($appraisal->user_id === $user->id) {
            return $appraisal->employee_signed_at === null;
        }

        if ($appraisal->reviewer_id === $user->id) {
            return $appraisal->reviewer_signed_at === null;
        }

        return false;
    }

    /**
     * Reviewer or admin may capture one or both signatures on an in-person "assisted"
     * sign-off screen, for an employee who has no portal access of their own.
     */
    public function signOnBehalf(User $user, Appraisal $appraisal): bool
    {
        if ($appraisal->status !== AppraisalStatus::PendingSignoff) {
            return false;
        }

        if ($appraisal->isFullySigned()) {
            return false;
        }

        return $appraisal->reviewer_id === $user->id || $user->isAdmin();
    }

    /**
     * Admin may send an appraisal back to an earlier stage (e.g. to let the employee
     * or reviewer correct a mistake after submitting). Not available while still
     * Draft or Awaiting Employee — there's nothing to send back from there.
     */
    public function reopen(User $user, Appraisal $appraisal): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return ! in_array($appraisal->status, [AppraisalStatus::Draft, AppraisalStatus::PendingEmployee], true);
    }
}

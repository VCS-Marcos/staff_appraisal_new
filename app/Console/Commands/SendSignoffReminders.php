<?php

namespace App\Console\Commands;

use App\Enums\AppraisalStatus;
use App\Models\Appraisal;
use App\Notifications\AppraisalSignoffReminder;
use Illuminate\Console\Command;

class SendSignoffReminders extends Command
{
    protected $signature = 'appraisals:remind-signoff';

    protected $description = 'Notify whichever party (employee/reviewer) has not yet signed an appraisal pending sign-off';

    public function handle(): int
    {
        $appraisals = Appraisal::where('status', AppraisalStatus::PendingSignoff)
            ->with(['employee', 'reviewer', 'cycle'])
            ->get();

        $sent = 0;

        foreach ($appraisals as $appraisal) {
            if ($appraisal->employee_signed_at === null) {
                $appraisal->employee->notify(new AppraisalSignoffReminder($appraisal));
                $sent++;
            }

            if ($appraisal->reviewer_signed_at === null) {
                $appraisal->reviewer->notify(new AppraisalSignoffReminder($appraisal));
                $sent++;
            }
        }

        $this->info("Sent {$sent} sign-off reminder(s) across {$appraisals->count()} pending appraisal(s).");

        return self::SUCCESS;
    }
}

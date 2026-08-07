<?php

namespace App\Notifications;

use App\Models\Appraisal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppraisalReadyForSignoff extends Notification
{
    public function __construct(protected Appraisal $appraisal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appraisal ready for sign-off')
            ->greeting("Hi {$notifiable->name},")
            ->line("The appraisal for {$this->appraisal->employee->name} — {$this->appraisal->cycle->name} ({$this->appraisal->cycle->term->value}) — is complete and ready for sign-off.")
            ->action('Review & Sign', route('appraisals.sign', $this->appraisal));
    }
}

<?php

namespace App\Notifications;

use App\Models\Appraisal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppraisalSignoffReminder extends Notification
{
    public function __construct(protected Appraisal $appraisal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reminder: appraisal awaiting your signature')
            ->greeting("Hi {$notifiable->name},")
            ->line("The appraisal for {$this->appraisal->employee->name} — {$this->appraisal->cycle->name} ({$this->appraisal->cycle->term->value}) — is still awaiting your signature.")
            ->action('Sign Now', route('appraisals.sign', $this->appraisal));
    }
}

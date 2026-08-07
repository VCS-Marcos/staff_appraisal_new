<?php

namespace App\Notifications;

use App\Models\Appraisal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppraisalOpened extends Notification
{
    public function __construct(protected Appraisal $appraisal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your appraisal is now open')
            ->greeting("Hi {$notifiable->name},")
            ->line("Your appraisal for {$this->appraisal->cycle->name} ({$this->appraisal->cycle->term->value}) is now open.")
            ->line('Please review your targets and complete your self-reflection.')
            ->action('Complete My Appraisal', route('appraisals.edit', $this->appraisal));
    }
}

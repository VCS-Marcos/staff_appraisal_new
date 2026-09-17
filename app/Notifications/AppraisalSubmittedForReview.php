<?php

namespace App\Notifications;

use App\Models\Appraisal;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppraisalSubmittedForReview extends Notification
{
    public function __construct(protected Appraisal $appraisal) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('An appraisal is ready for your review')
            ->greeting("Hi {$notifiable->name},")
            ->line("{$this->appraisal->employee->name} has submitted their self-reflection for their {$this->appraisal->year} appraisal.")
            ->line('Please add your comments, set an overall rating, and submit your review.')
            ->action('Review Appraisal', route('appraisals.review', $this->appraisal));
    }
}

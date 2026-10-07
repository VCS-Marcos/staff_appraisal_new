<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sends a notification without ever letting a mail problem (SMTP down, wrong password,
 * rate limit) break the action that triggered it. A failure is logged, written to the
 * Activity Log, and flashed as a warning so the person doing the action knows the
 * recipient wasn't emailed.
 */
class SafeNotify
{
    public static function send(User $recipient, Notification $notification): bool
    {
        try {
            $recipient->notify($notification);

            return true;
        } catch (Throwable $e) {
            $type = class_basename($notification);

            Log::error('Email notification failed', [
                'notification' => $type,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);

            AuditLog::record('notification.failed', $recipient, "Email ({$type}) to {$recipient->name} could not be sent");

            if (app()->bound('request') && request()->hasSession()) {
                $names = collect(session('warning_recipients', []))->push($recipient->name)->unique()->values()->all();
                session()->flash('warning_recipients', $names);
            }

            return false;
        }
    }
}

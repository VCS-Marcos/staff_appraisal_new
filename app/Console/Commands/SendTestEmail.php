<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test {to : Address to send the test message to}';

    protected $description = 'Send a test email using the current mail settings, to check SMTP is working';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $this->line("Mailer: {$mailer}");

        if ($mailer === 'smtp') {
            $cfg = config('mail.mailers.smtp');
            $this->line("Server: {$cfg['host']}:{$cfg['port']}  (scheme: ".($cfg['scheme'] ?? 'default').')');
            $this->line('Username: '.($cfg['username'] ?: '(none)'));
            $this->line('Password: '.(filled($cfg['password']) ? 'set' : 'NOT SET'));
        }

        $this->line('From: '.config('mail.from.address'));

        try {
            Mail::raw(
                'This is a test email from the Staff Appraisal System. If you can read this, outgoing email is working.',
                fn ($m) => $m->to($this->argument('to'))->subject('Staff Appraisal System - test email'),
            );
        } catch (Throwable $e) {
            $this->error('FAILED: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info($mailer === 'smtp'
            ? "Accepted by the mail server. Check {$this->argument('to')} (and its spam folder)."
            : "Mailer is '{$mailer}', so nothing was actually delivered (see storage/logs/laravel.log).");

        return self::SUCCESS;
    }
}

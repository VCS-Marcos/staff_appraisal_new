<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Reset your password')
                ->greeting("Hi {$notifiable->name},")
                ->line('We received a request to reset the password for your Staff Appraisal account.')
                ->action('Reset Password', $url)
                ->line("This link will expire in {$minutes} minutes.")
                ->line('If you did not request a password reset, no action is needed — your password has not been changed.');
        });

        Password::defaults(fn () => Password::min(10)->mixedCase()->numbers());
    }
}

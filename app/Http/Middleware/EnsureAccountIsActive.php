<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Deactivating a staff account (Admin > All Staff) doesn't touch any
     * session already open for it — without this, someone deactivated
     * mid-session keeps full access until that session naturally expires.
     * This ends it on their very next request instead.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account has been deactivated. Contact your administrator.',
            ]);
        }

        return $next($request);
    }
}

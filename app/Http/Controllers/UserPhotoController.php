<?php

namespace App\Http\Controllers;

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UserPhotoController extends Controller
{
    public function show(User $user): Response
    {
        $this->authorize('viewPhoto', $user);

        $photo = $user->photo;

        abort_if($photo === null, 404);

        return response($photo->data, 200, [
            'Content-Type' => 'image/jpeg',
            'Content-Disposition' => 'inline; filename="staff-photo.jpg"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            // Private: staff photos must never be stored by shared proxies. The URL is
            // versioned, so a replaced photo gets a new URL and can be cached safely here.
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    // Ends the session of a user who was deactivated while logged in
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()?->deleted_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Akun Anda sudah dinonaktifkan.';
            return $request->expectsJson()
                ? response()->json(['message' => $message], 401)
                : redirect()->route('login')->withErrors(['auth' => $message]);
        }

        return $next($request);
    }
}

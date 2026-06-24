<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'username'    => ['required'],
                'password' => ['required'],
            ]);

            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'auth' => ['Username atau password salah.']
                ]);
            }

            $request->session()->regenerate();
            return response()->json(['redirect' => route('dashboard'), 'message' => 'Login berhasil.']);
        } catch (ValidationException $e) {
            Log::error('Error LoginController@login: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error LoginController@login: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Terjadi kesalahan saat mencoba login. Silakan coba lagi.'], 500);
        }
    }
}

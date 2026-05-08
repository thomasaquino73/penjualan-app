<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Cari user by email (case-insensitive)
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        // Kalau email tidak ditemukan
        if (! $user) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Email tidak ditemukan',
                    'errors' => [
                        'email' => ['Email tidak terdaftar dalam sistem'],
                    ],
                ], 422);
            }

            throw ValidationException::withMessages([
                'email' => [__('Email tidak terdaftar dalam sistem')],
            ]);
        }

        // Kalau email belum diverifikasi

        if (empty($user->email_verified_at)) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Email belum diverifikasi',
                    'redirect' => route('guest.verification'),
                    'errors' => [
                        'email' => ['Email belum diverifikasi. Silakan verifikasi email terlebih dahulu.'],

                    ],
                ], 422);
            }

            // redirect ke halaman verifikasi
            return redirect()->route('guest.verification.resend')
                ->with('warning', 'Email belum diverifikasi. Silakan verifikasi email terlebih dahulu.');
        }

        // Kirim reset password link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($request->ajax()) {
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => __($status),
                ]);
            }

            return response()->json([
                'message' => __($status),
                'errors' => [
                    'email' => [__($status)],
                ],
            ], 422);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }
}

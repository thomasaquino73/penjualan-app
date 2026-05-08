<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    protected function authenticated(Request $r, $user)
    {
        if ($user->status == 'Not Active') {
            Auth::logout();

            return redirect()->route('login')->withErrors(['status' => 'Your account is not active.']);
        }
    }

    public function create(): View
    {
        $backgrounds = DB::table('login_background')->where('status', 1)->get();

        $x = [
            'backgrounds' => $backgrounds,
        ];

        return view('auth.login', $x);
    }

    public function store(LoginRequest $r): RedirectResponse|JsonResponse
    {
        try {
            $remember = $r->boolean('remember');
            $login = $r->input('username'); // tetap pakai field 'username'
            $password = $r->input('password');

            // Tentukan kolom login: email atau username
            $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            // 🔍 Cari user dengan perbandingan case-sensitive
            $user = User::whereRaw("BINARY {$field} = ?", [$login])->first();

            // Jika user tidak ditemukan atau password salah
            if (! $user || ! Hash::check($password, $user->password)) {
                return $r->wantsJson()
                    ? response()->json(['message' => 'Login gagal, periksa kembali kredensial Anda.'], 422)
                    : back()->withErrors(['username' => 'Login gagal, periksa kembali kredensial Anda.']);
            }

            // 🔒 Cek status aktif akun
            if ($user->active == 0 || $user->status == 'Not Active') {
                Auth::logout();

                return $r->wantsJson()
                    ? response()->json([
                        'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                        'status_code' => 'inactive_account',
                    ], 403)
                    : redirect()->route('login')->withErrors([
                        'username' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    ]);
            }

            // 🔒 Validasi status verifikasi email
            if (is_null($user->email_verified_at)) {
                Auth::logout();

                return response()->json([
                    'status_code' => 'unverified_email',
                    'message' => 'Email Anda belum diverifikasi. Klik OK untuk mengirim ulang link verifikasi.',
                ], 403);
            }

            // ✅ Jika tanggal verifikasi di masa depan → belum boleh login
            if ($user->email_verified_at->isAfter(now())) {
                Auth::logout();

                return $r->wantsJson()
                    ? response()->json([
                        'message' => 'Akun Anda belum aktif hingga tanggal '.$user->email_verified_at->format('d M Y H:i').'.',
                        'status_code' => 'email_not_active_yet',
                    ], 403)
                    : redirect()->route('login')->withErrors([
                        'email' => 'Akun Anda belum aktif. Silakan login kembali pada tanggal '.
                            $user->email_verified_at->format('d M Y H:i').'.',
                    ]);
            }

            // 🔑 Login manual (karena tidak pakai Auth::attempt)
            Auth::login($user, $remember);

            // Regenerasi session
            $r->session()->regenerate();
            $r->session()->put('last_login', now());
            $r->session()->put('role', $user->role);
            $r->session()->put('ip', $r->ip());
            $r->session()->put('user_id', $user->id);

            // Reset dan buat token baru
            $user->tokens()->delete();
            $token = $user->createToken('login_token')->plainTextToken;

            if ($r->wantsJson()) {
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'redirect' => redirect()->intended(route('dashboard', absolute: false))->getTargetUrl(),
                ]);
            }

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (ValidationException $e) {
            if ($r->wantsJson()) {
                return response()->json([
                    'message' => 'Login failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $r): RedirectResponse|JsonResponse
    {
        $user = $r->user();
        if ($user) {
            $user->update([
                'last_seen' => now(),
            ]);
        }
        if ($r->user() && $r->user()->currentAccessToken()) {
            $r->user()->currentAccessToken()->delete();
        }
        if ($user) {
            $userId = $user->id;

            // Hapus cache online per user
            Cache::forget('user-is-online-'.$userId);

            // Hapus dari daftar 'online-users'
            $onlineUsers = Cache::get('online-users', []);
            $onlineUsers = array_filter($onlineUsers, fn ($id) => $id != $userId);
            Cache::put('online-users', $onlineUsers, now()->addSeconds(config('session.online_duration', 15)));
        }
        Auth::guard('web')->logout();

        $r->session()->invalidate();
        $r->session()->regenerateToken();

        if ($r->wantsJson()) {
            return response()->json([
                'message' => 'Logged out successfully',
                'redirect' => url('/'),
            ]);
        }

        return redirect('/login')->with('logout_message', 'Anda telah berhasil Logout.');
    }
}

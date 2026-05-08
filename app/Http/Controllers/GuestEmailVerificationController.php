<?php

namespace App\Http\Controllers;

use App\Mail\GuestVerificationMail;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class GuestEmailVerificationController extends Controller
{
    public function index(Request $r)
    {
        return view('auth.kirim-ulang-password');
    }

    public function resend(Request $r): JsonResponse
    {
        $r->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $r->email)->first();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email tidak ditemukan',
            ], 422);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'info',
                'message' => 'Email sudah diverifikasi. Silakan login.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => 'success',
            'message' => 'Link verifikasi telah dikirim ulang ke email Anda.',
        ]);
    }

    public function verify($encryptedId)
    {
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Link verifikasi tidak valid.');
        }

        $user = User::find($id);

        if (! $user) {
            return redirect()->route('login')->with('error', 'Data pengguna tidak ditemukan.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->route('login')->with('success', 'Email berhasil diverifikasi!');
    }

    public function sendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|string', // bisa username atau email
        ]);

        $input = $request->email;

        // Cek apakah input adalah email atau username
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari user berdasarkan kolom yang sesuai
        $user = User::where($field, $input)->first();

        if (! $user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
        }

        // Buat link verifikasi pakai ID terenkripsi
        $encryptedId = Crypt::encryptString($user->id);
        $verificationUrl = route('guest.verify.email', ['id' => $encryptedId]);

        // Kirim email verifikasi
        Mail::to($user->email)->send(new GuestVerificationMail($user, $verificationUrl));

        return response()->json(['message' => 'Email verifikasi telah dikirim.']);
    }
}

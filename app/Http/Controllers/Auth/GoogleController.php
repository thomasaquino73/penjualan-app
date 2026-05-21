<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $baseUsername = Str::slug($googleUser->getName(), ''); 
                $username = $baseUsername . '_' . Str::lower(Str::random(4));

                while (User::where('username', $username)->exists()) {
                    $username = $baseUsername . '_' . Str::lower(Str::random(4));
                }

                $user = User::create([
                    'username'          => $username,
                    'email'             => $googleUser->getEmail(),
                    'password'          => Hash::make(Str::random(24)), 
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user, true); 
            request()->session()->regenerate();

            return redirect('/dashboard');

        } catch (Exception $e) {
            // Kita potong pesan error asli bawaan sistem, 
            // lalu kita paksa ganti dengan teks pesananmu di sini:
            return redirect('/login')->with('google_error', 'Email not found or authentication failed. Please try again with a different Google account.');
        }
    }
}
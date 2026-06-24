<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Models\PengaturanSistem;
use App\Models\Setting\Company;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = User::findOrFail(Auth::id());

        return view('profile.profile_index', [
            'title' => 'User Profile',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'User Profile', 'url' => ''],
            ],
            'user' => $user,
        ]);
    }

    public function change_password()
    {
        $userID = Auth::User()->id;
        $user = User::where('id', $userID)->first();
        $x = [
            'title' => 'Change Password',
            'breadcrumb' => [
                ['label' => 'User Profile', 'url' => route('profile.index')],
                ['label' => 'Change Password', 'url' => ''],
            ],
            'user' => $user,

        ];

        return view('profile.profile_changepassword', $x);
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/foto_user';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function ganti_password(PasswordRequest $r)
    {
        try {
            $user = Auth::user();

            if (! Hash::check($r->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini salah.'])->withInput();
            }
            if ($r->hasFile('avatar')) {
                $user['avatar'] = $this->uploadAvatar($r->file('avatar'));
            }

            if ($r->filled('username') && $r->username !== $user->username) {
                $user->username = $r->username;
            }

            if ($r->filled('email') && $r->email !== $user->email) {
                $user->email = $r->email;
            }

            if ($r->filled('password')) {
                $user->password = Hash::make($r->password);
            }
            if ($r->user()->isDirty('email')) {
                $r->user()->email_verified_at = null;
            }

            $user->save();

            Auth::logout();

            if ($r->ajax()) {
                return response()->json([
                    'message' => 'Password dan email berhasil diperbarui.',
                    'status' => 'success',
                    'redirect' => route('login'),
                ], 200);
            } else {
                return redirect()->route('login')->with('success', 'Data berhasil diperbarui. Silakan login kembali.');
            }

        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Terjadi kesalahan: '.$e->getMessage()])->withInput();
        }
    }

  public function cetak($id)
{
    $user = User::findOrFail(Auth::user()->id);
    $company = Company::first();

    // Logo perusahaan
    $logoBase64 = null;
    if ($company && $company->logo) {
        $path = public_path($company->logo);

        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/' . $type . ';base64,' .
                base64_encode(file_get_contents($path));
        }
    }

    // Background kartu
    $backgroundBase64 = null;
    $backgroundPath = public_path('image/logo/backgroundkartu.png');

    if (file_exists($backgroundPath)) {
        $backgroundBase64 = 'data:image/png;base64,' .
            base64_encode(file_get_contents($backgroundPath));
    }

    // Avatar user
    if (
        $user->avatar &&
        file_exists(public_path($user->avatar))
    ) {
        $avatarPath = public_path($user->avatar);
    } else {
        $avatarPath = $user->gender == 'Male'
            ? public_path('image/foto_user/avatar_user_default.png')
            : public_path('image/foto_user/avatar_women.png');
    }

    $avatarBase64 = null;

    if (file_exists($avatarPath)) {
        $type = pathinfo($avatarPath, PATHINFO_EXTENSION);

        $avatarBase64 = 'data:image/' . $type . ';base64,' .
            base64_encode(file_get_contents($avatarPath));
    }

    $data = [
        'user' => $user,
        'company' => $company,
        'logoBase64' => $logoBase64,
        'avatar' => $avatarBase64,
        'backgroundBase64' => $backgroundBase64,
    ];

    $pdf = Pdf::loadView('profile.kartu_anggota', $data)
          ->setPaper([0, 0, 242.65, 153.07]);

    return $pdf->stream('kartu-anggota.pdf');
}
}

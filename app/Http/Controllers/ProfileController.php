<?php

namespace App\Http\Controllers;

use App\Http\Requests\PasswordRequest;
use App\Models\PengaturanSistem;
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
        $user = User::where('id', $id)->first();
        $company = PengaturanSistem::find(1);

        $data = [
            'user' => $user,
            'company' => $company,
        ];

        $pdf = Pdf::loadView('profile.kartu_anggota', $data)
            ->setPaper([0, 0, 242.65, 153.07]);

        return $pdf->stream('kartu-anggota.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
         $data = User::query();
        $stats = $this->getUserStatistics($data);

        $x = [
            'title' => 'Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => '', 'url' => ''],
            ],
            'totalUsers' => $stats['totalUsers'],
                'totalActive' => $stats['totalActive'],
                'totalVerified' => $stats['totalVerified'],
                'totalLogin' => $stats['totalLogin'],
        ];

        return view('dashboard', $x);
    }

    private function getUserStatistics($data)
    {
        $allUsers = $data->get();

        return [
            'totalUsers' => User::where('active', 1)->count(),

            'totalActive' => User::where('status', 'Active')
                ->where('active', 1)
                ->count(),

            'totalVerified' => User::whereNotNull('email_verified_at')
                ->where('active', 1)
                ->count(),

            'totalLogin' => $allUsers->filter(function ($user) {
                return Cache::has('user-is-online-'.$user->id);
            })->count(),
        ];
    }
}

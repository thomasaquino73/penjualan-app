<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(){
        $x=[
            'title' => 'Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => '', 'url' => ''],
            ],
        ];
        return view('dashboard',$x);
    }
}

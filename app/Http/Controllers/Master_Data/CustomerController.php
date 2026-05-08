<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    Public function index()
    {
        $x=[
            'title' => 'Customer List',
              'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer', 'url' => ''],
            ],
        ];
        return view('master_data.customer.customer_index', $x);
    }
}

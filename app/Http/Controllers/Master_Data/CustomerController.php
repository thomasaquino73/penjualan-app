<?php

namespace App\Http\Controllers\Master_Data;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
    {
        $x = [
            'title' => 'Customer List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_index', $x);
    }

    public function create()
    {
        $x = [
            'title' => 'Add Customer',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Customer', 'url' => route('customer.index')],
                ['label' => 'Add Customer', 'url' => ''],
            ],
        ];

        return view('master_data.customer.customer_create', $x);
    }
}

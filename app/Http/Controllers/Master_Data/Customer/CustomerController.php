<?php

namespace App\Http\Controllers\Master_Data\Customer;

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
    public function create()
    {
        $x=[
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

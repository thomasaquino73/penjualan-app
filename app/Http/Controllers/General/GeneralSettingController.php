<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'general-setting.index' => 'general-browse',
                'general-setting.show' => 'general-read',
                'general-setting.create' => 'general-create',
                'general-setting.store' => 'general-create',
                'general-setting.edit' => 'general-edit',
                'general-setting.update' => 'general-edit',
                'general-setting.destroy' => 'general-delete',
                'general-setting.trash' => 'general-trash',
                'general-setting.restore' => 'general-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }
       public function index(){
        
        $x = [
            'title' => 'General Setting',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'General Setting', 'url' => ''],
            ],
        ];

        return view('general.general-setting.general_setting_index', $x);
    }
}

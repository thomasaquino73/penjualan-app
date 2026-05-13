<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyInfoRequest;
use App\Models\BasicCodeDetail;
use App\Models\General\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyInfoController extends Controller
{
    public function index(){
        $sistemID = 1;
        $sistem = Company::findOrFail($sistemID);
        $x = [
            'title' => 'Company Information',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Company Information', 'url' => ''],
            ],
                'dataSistem' => $sistem,
        ];

        return view('general.company-info.company_info_index', $x);
    }
    public function edit($id)
    {
        $sistem = Company::findOrFail($id);
        $currenci = BasicCodeDetail::where('master_id', 3)->get();

        $x = [
            'title' => 'Company Information',
            'dataSistem' => $sistem,
            'currencies' => $currenci,
            'breadcrumb' => [
                ['label' => 'Company Information', 'url' => route('company.info')],
                ['label' => 'Edit Company Information', 'url' => ''],
            ],
        ];

        return view('general.company-info.company_info_edit', $x);
    }
      private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/logo';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function update(CompanyInfoRequest $r, $id)
    {
        DB::beginTransaction();

        try {
            $sistem = Company::findOrFail($id);

            $data = $r->except('avatar');

            if ($r->hasFile('avatar')) {

                $logoPath = $this->uploadAvatar($r->file('avatar'));

                $data['logo'] = $logoPath;
                $data['favicon'] = $logoPath;
            }

            $sistem->update($data);

            DB::commit();

            return response()->json([
                'title' => 'Success',
                'message' => 'Company information updated successfully.',
                'redirect' => route('company.info'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

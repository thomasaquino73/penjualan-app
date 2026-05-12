<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'roles.index' => 'role-browse',
                'roles.store' => 'role-create',
                'roles.show' => 'role-read',
                'roles.edit' => 'role-edit',
                'roles.destroy' => 'role-delete',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $r)
    {
        if ($r->ajax()) {

            $query = Roles::with(['creator', 'updater'])
                ->get()
                ->map(function ($role) {

                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'status' => $role->status ?? 1, // kalau ada kolom status
                        'created_by' => $role->creator->fullname ?? 'System',
                        'updated_by' => $role->updater->fullname ?? 'System',
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ];
                })
                ->sortBy('id');

            return datatables()
                ->of($query)
                ->addIndexColumn()

                ->editColumn('created_at', function ($row) {
                    return $row['created_by']
                        .'<br><small class="text-muted">'
                        .optional($row['created_at'])->diffForHumans()
                        .'</small>';
                })

                ->editColumn('updated_at', function ($row) {
                    return $row['updated_by']
                        .'<br><small class="text-muted">'
                        .optional($row['updated_at'])->diffForHumans()
                        .'</small>';
                })
                ->addColumn('action', function ($row) {

                    // 🔥 lock Super Admin
                    if (strtolower($row['name']) === 'super admin') {
                        return '<span class="text-danger fw-bold">This data cannot be modified</span>';
                    }

                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="ti ti-menu-2 ti-xs me-1"></i> </button>';
                    $btn .= '<div class="dropdown-menu">';

                    if (auth()->user()->can('role-edit')) {
                        $btn .= '<a class="dropdown-item editPost" href="javascript:void(0)" data-id="'.$row['id'].'">
                                    <i class="ti ti-edit me-1"></i> Edit</a>';
                    }
                    if (auth()->user()->can('permission-edit')) {
                        $btn .= '<a class="dropdown-item "  href="'.route('permissions.edit', $row['id']).'" data-id="'.$row['id'].'">
                                    <i class="ti ti-shield-share me-1"></i> Permission</a>';
                    }

                    if (auth()->user()->can('role-delete')) {
                        $btn .= '<a class="dropdown-item" id="delete" href="javascript:void(0)" data-id="'.$row['id'].'" data-name="'.$row['name'].'">
                                    <i class="ti ti-trash me-1"></i> Delete</a>';
                    }

                    $btn .= '</div></div>';

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status'])
                ->make(true);
        }

        return view('pengaturan.role.role_index', [
            'title' => 'Roles',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Roles', 'url' => ''],
            ],
        ]);
    }

    public function store(RoleRequest $request)
    {
        try {
            $id = $request->input('id');

            $data = [
                'name' => $request->name,
                'status' => $request->status,
                'updated_by' => Auth::user()->id,
            ];

            if (! empty($id)) {
                $role = Roles::findOrFail($id);
                $role->update($data);

                $message = 'Data has been updated successfully.';
                $title = 'Updated';
            } else {
                $data['created_by'] = Auth::user()->id;
                $role = Roles::create($data);

                $message = 'Data has been created successfully.';
                $title = 'Created';
            }

            return response()->json([
                'title' => $title,
                'message' => $message,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function edit(Request $request)
    {
        $role = Roles::findOrFail($request->id);

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'status' => $role->status,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        try {
            $role = Roles::findOrFail($id);

            $lockedRoles = ['super admin'];

            if (in_array(strtolower($role->name), $lockedRoles)) {
                return response()->json([
                    'error' => 'The role "'.$role->name.'" cannot be deleted.',
                ], 403);
            }

            $role->updated_by = Auth::user()->id;
            $role->save();

            // 🔥 ini yang bikin data benar-benar hilang
            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $table = Roles::findOrFail($id);
            $table->status = '2';
            $table->updated_by = Auth::user()->id;
            $table->save();
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}

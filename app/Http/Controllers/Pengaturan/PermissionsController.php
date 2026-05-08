<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class PermissionsController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Batasi hanya untuk Super Admin
            if (! $request->user()->hasRole('Super Admin')) {
                return response()->view('errors.403', [], 403);
            }

            // ✅ Harus kembalikan response ke Laravel pipeline
            return $next($request);
        });
    }

    public function index(Request $r)
    {
        if ($r->ajax()) {
            $query = Roles::query();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    return $row->status == 1
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at
                        ? (($row->creator->fullname ?? 'Unknown')).
                        ' <br><small class="text-muted"> '.$row->created_at->diffForHumans().'</small>'
                        : 'N/A';
                })
                ->addColumn('updated_at', function ($row) {
                    if ($row->updated_at) {
                        $updaterName = $row->updater->fullname ?? 'Unknown';
                        $timeAgo = $updaterName !== 'Unknown' ? $row->updated_at->diffForHumans() : 'N/A';

                        return $updaterName.
                            ' <br><small class="text-muted">'.$timeAgo.'</small>';
                    }

                    return 'N/A';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="btn-group">';
                    $btn .= '<button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-menu-2 ti-xs me-1"></i> Choose Options</button>';
                    $btn .= '<div class="dropdown-menu">';
                    if (auth()->user()->can('permission-edit')) {

                        $btn .= '<a class="dropdown-item" href="'.route('permissions.edit', $row->id).'"><i class="fa fa-edit me-1"></i>Edit</a>';
                    }
                    if (auth()->user()->can('permission-delete')) {
                        $btn .= '<a class="dropdown-item " id="delete" href="javascript:void(0)" data-id="'.$row->id.'"  data-name="'.$row->name.'">';
                        $btn .= '<i class="fa fa-trash me-1"></i> Delete</a>';
                    }

                    $btn .= '</div></div>';

                    return $btn;
                })
                ->rawColumns(['action', 'status', 'created_at', 'updated_at'])
                ->make(true);
        }

        $x = [
            'title' => 'Permissions',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Permissions', 'url' => ''],
            ],
        ];

        return view('pengaturan.permissions.permissions_index', $x);
    }

    public function create(Request $request) {}

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
                // Update existing record
                $role = Roles::findOrFail($id);
                $role->update($data);

                $title = 'Updated';
                $message = 'Data has been updated successfully.';
            } else {
                // Create new record
                $data['created_by'] = Auth::user()->id;
                $role = Roles::create($data);

                $title = 'Created';
                $message = 'Data has been created successfully.';
            }

            // Sync Role Groups (pivot table role_role_group)
            if ($request->has('role_group_id')) {
                $role->roleGroups()->sync($request->role_group_id);
            }

            return response()->json([
                'title' => $title,
                'message' => $message,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::orderBy('name', 'asc')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        // Group permissions berdasarkan role_group_id
        $groupedPermissions = [];
        foreach ($permissions as $perm) {
            $groupId = $perm->role_group_id ?? 0;

            // Ambil module dan action dari nama permission (seperti di kode kamu sebelumnya)
            if (str_contains($perm->name, '.')) {
                [$module, $action] = explode('.', $perm->name, 2);
            } elseif (str_contains($perm->name, '-')) {
                [$module, $action] = explode('-', $perm->name, 2);
            } else {
                $module = $perm->name;
                $action = 'custom';
            }

            $groupedPermissions[$groupId][$module][$action] = $perm;
        }

        // Ambil semua action unik
        $actions = collect($permissions)
            ->map(function ($p) {
                if (str_contains($p->name, '.')) {
                    return explode('.', $p->name, 2)[1];
                } elseif (str_contains($p->name, '-')) {
                    return explode('-', $p->name, 2)[1];
                }

                return 'custom';
            })
            ->unique()
            ->values()
            ->toArray();
        $roles = Role::orderBy('name', 'asc')->get();

        return view('pengaturan.permissions.permissions_edit', [
            'title' => 'Edit Permissions',
            'breadcrumb' => [
                ['label' => 'Permissions', 'url' => route('permissions.index')],
                ['label' => 'Edit Permissions', 'url' => ''],
            ],
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
            'actions' => $actions,
            'rolePermissions' => $rolePermissions,
            'roles' => $roles,

        ]);
    }

    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            $role = Role::findOrFail($id);

            // Basic validation
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'integer|exists:permissions,id',
            ]);

            // Update role name + updated_by
            $role->update([
                'name' => $validated['name'],
                'updated_by' => Auth::id(),
            ]);

            // Get permissions from request
            $permissions = Permission::whereIn('id', $validated['permissions'] ?? [])->get();

            // Sync permissions
            $role->syncPermissions($permissions);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role has been updated successfully.',
                'redirect' => route('permissions.index'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $role = Roles::findOrFail($id);

            // Cegah Super Admin
            if (strtolower($role->name) === 'super admin') {
                return response()->json([
                    'message' => 'Role "Super Admin" cannot be deleted.',
                ], 403);
            }

            // 🔥 HARD DELETE (BENAR-BENAR HILANG)
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

    public function getRolePermissions($id)
    {
        $role = Role::findOrFail($id);
        $permissions = $role->permissions->pluck('id')->toArray();

        return response()->json([
            'success' => true,
            'permissions' => $permissions,
        ]);
    }
}

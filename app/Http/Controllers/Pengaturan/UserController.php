<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Http\Requests\PenggunaRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $routeName = $request->route()->getName();

            $permissionMap = [
                'user.index' => 'user-browse',
                'user.show' => 'user-read',
                'user.create' => 'user-create',
                'user.store' => 'user-create',
                'user.edit' => 'user-edit',
                'user.update' => 'user-edit',
                'user.destroy' => 'user-delete',
                'user.trash' => 'user-trash',
                'user.restore' => 'user-restore',
            ];

            if (isset($permissionMap[$routeName])) {
                if (! $request->user()->can($permissionMap[$routeName])) {
                    abort(403, 'Unauthorized action');
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data = User::with(['roles', 'creator', 'updater'])->where('active', '1');
        if ($request->status) {
            $data->where('status', $request->status);
        }
        if ($request->verify == 'Verify') {
            $data->whereNotNull('email_verified_at');
        }

        if ($request->verify == 'Not Verify') {
            $data->whereNull('email_verified_at');
        }

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return $row->created_at
                        ? (optional($row->creator)->nama_lengkap ?? 'Unknown').
                            ' <br><small class="text-muted"> '.$row->created_at->diffForHumans().'</small>'
                        : 'N/A';
                })
                ->addColumn('updated_at', function ($row) {
                    if ($row->updated_at) {
                        $updaterName = $row->updater->nama_lengkap ?? 'Unknown';
                        $timeAgo = $updaterName !== 'Unknown' ? $row->updated_at->diffForHumans() : 'N/A';

                        return $updaterName.
                            ' <br><small class="text-muted">'.$timeAgo.'</small>';
                    }

                    return 'N/A';
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 'Active' ? '<span class="badge bg-success">'.$row->status.'</span>' : '<span class="badge bg-danger">'.$row->status.'</span>';
                })
                ->addColumn('avatar', function ($row) {

                    // Tentukan avatar berdasarkan data user
                    if ($row->avatar) {
                        $avatarUrl = asset($row->avatar);
                    } else {
                        $avatarUrl = $row->gender == 'Perempuan'
                            ? asset('image/foto_user/avatar_women.png')
                            : asset('image/foto_user/avatar_user_default.png');
                    }

                    // Kembalikan HTML img untuk datatable
                    return '<img class="avatar avatar-md rounded-circle me-2 avatar-online"
                                src="'.$avatarUrl.'"
                                alt="Pengguna profile picture">';
                })

                ->addColumn('roles', function ($row) {
                    $roles = '';  // Initialize an empty string to accumulate role badges
                    if (! empty($row->getRoleNames())) {
                        foreach ($row->getRoleNames() as $v) {
                            if ($v != 'Warga') {
                                $roles .= '<span class="badge bg-info">'.$v.'</span> ';
                            } else {
                                // Add a regular badge for other roles
                                $roles .= '<span class="badge bg-success">'.$v.'</span> ';
                            }
                        }
                    }

                    return $roles;  // Return the accumulated roles outside the loop
                })
                ->addColumn('last_seen', function ($row) {
                    if (method_exists($row, 'isOnline') && $row->isOnline()) {
                        return '<span class="badge bg-success">Online Now</span>';
                    } elseif ($row->last_seen) {
                        return '<span class="badge bg-info">'
                            .Carbon::parse($row->last_seen)->diffForHumans()
                            .'</span>';
                    }

                    return '<span class="badge bg-secondary">No Data</span>';
                })
                ->addColumn('action', function ($row) {

                    $icon = is_null($row->email_verified_at)
                        ? '<i class="ti ti-user-check ti-xs me-1"></i>'
                        : '<i class="ti ti-menu-2 ti-xs me-1"></i>';

                    $color = is_null($row->email_verified_at) ? 'warning' : 'primary';

                    $btn = '
        <div class="btn-group">
            <button type="button"
                class="btn btn-'.$color.' dropdown-toggle waves-effect waves-light"
                data-bs-toggle="dropdown" aria-expanded="false">
                '.$icon.' 
            </button>
            <div class="dropdown-menu">
     ';

                    // ✅ EDIT
                    if (auth()->user()->can('user-edit')) {
                        $btn .= '
            <a class="dropdown-item has-icon" href="'.route('user.edit', $row->id).'">
                <i class="far fa-edit"></i> Edit
            </a>
        ';
                    }

                    // ✅ DETAIL
                    if (auth()->user()->can('user-read')) {
                        $btn .= '
            <a class="dropdown-item has-icon" href="'.route('user.show', $row->id).'">
                <i class="far fa-eye"></i> Detail
            </a>
        ';
                    }

                    // ✅ DELETE
                    if (auth()->user()->can('user-delete')) {
                        $btn .= '
            <a class="dropdown-item has-icon" href="javascript:void(0)" id="delete"
                data-id="'.$row->id.'"
                data-name="'.$row->fullname.'">
                <i class="fa fa-trash me-1"></i> Delete
            </a>
        ';
                    }

                    // ✅ VERIFY USER
                    if (is_null($row->email_verified_at) && auth()->user()->can('user-verify')) {
                        $btn .= '
            <a class="dropdown-item has-icon" href="javascript:void(0)" id="verify"
                data-id="'.$row->id.'"
                data-name="'.$row->fullname.'">
                <i class="ti ti-user-check"></i> Verify User
            </a>
        ';
                    }

                    $btn .= '</div></div>';

                    return $btn;
                })

                ->rawColumns(['action', 'created_at', 'updated_at', 'status', 'roles', 'last_seen', 'avatar'])
                ->make(true);
        }
        $stats = $this->getUserStatistics($data);
        $x = [
            'title' => 'User List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Users', 'url' => ''],
            ],
            'totalUsers' => $stats['totalUsers'],
            'totalActive' => $stats['totalActive'],
            'totalVerified' => $stats['totalVerified'],
            'totalLogin' => $stats['totalLogin'],

        ];

        return view('pengaturan.user.user_index', $x);
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

    public function create()
    {
        $roles = Role::get();

        return view('pengaturan.user.user_create', [
            'title' => 'Add User',
            'breadcrumb' => [
                ['label' => 'User', 'url' => route('user.index')],
                ['label' => 'Add User', 'url' => ''],
            ],
            'roles' => $roles,
        ]);
    }

    private function uploadAvatar($avatar)
    {
        $name = uniqid().time();
        $destination = 'image/foto_user';
        $filePath = $avatar->move($destination, $name.'.'.$avatar->getClientOriginalExtension());

        return str_replace('\\', '/', $filePath);
    }

    public function store(PenggunaRequest $r)
    {
        DB::beginTransaction();

        try {
            $userData = $r->except('confirm_password', 'roles');

            $userData['password'] = Hash::make($r->password);
            $userData['created_by'] = Auth::id();
            $userData['email'] = strtolower($r->email);

            if ($r->hasFile('avatar')) {
                $userData['avatar'] = $this->uploadAvatar($r->file('avatar'));
            }

            $roleName = $r->roles;
            $role = Role::where('name', $roleName)->first();

            if (! $role) {
                return response()->json([
                    'errors' => [
                        'roles' => ['The selected role is invalid.'],
                    ],
                ], 422);
            }

            $userData['role_group_id'] = $role->id;

            $user = User::create($userData);

            $user->syncRoles([$roleName]);

            DB::commit();

            return response()->json([
                'title' => 'Success',
                'message' => 'User has been created successfully and role has been assigned.',
                'redirect' => route('user.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'errors' => [
                    'general' => ['An error occurred: '.$e->getMessage()],
                ],
            ], 500);
        }
    }

    public function show(string $id)
    {
        $x = [
            'title' => 'User Detail',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'User Detail', 'url' => ''],
            ],
            'user' => User::with(['roles', 'creator', 'updater'])->findOrFail($id),
        ];

        return view('pengaturan.user.user_show', $x);
    }

    public function edit($id)
    {
        $account = User::findOrFail($id);

        $roles = Role::all();
        $userRoles = $account->getRoleNames()->toArray();

        return view('pengaturan.user.user_edit', [
            'title' => 'Change User',
            'breadcrumb' => [
                ['label' => 'User', 'url' => route('user.index')],
                ['label' => 'Change User', 'url' => ''],
            ],
            'account' => $account,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ]);
    }

    public function update(PenggunaRequest $r, $id)
    {
        DB::beginTransaction();

        try {
            $user = User::findOrFail($id);

            $userData = $r->except('password', 'confirm_password', 'roles', 'permission');
            $userData['updated_by'] = Auth::id();
            $userData['updated_at'] = now();

            // Upload avatar
            if ($r->hasFile('avatar')) {
                $userData['avatar'] = $this->uploadAvatar($r->file('avatar'));
            }

            // Login email
            if ($r->filled('email')) {
                $loginInput = strtolower($r->input('email'));

                if ($loginInput !== $user->email) {
                    $userData['email'] = $loginInput;
                    $userData['email_verified_at'] = null;
                }
            }

            // Update password
            if ($r->filled('password')) {
                $userData['password'] = Hash::make($r->input('password'));
            }

            // Role assignment
            $roleName = $r->input('roles');
            if ($roleName) {
                $role = Role::where('name', $roleName)->first();

                if (! $role) {
                    return response()->json([
                        'error' => 'The selected role is invalid.',
                    ], 422);
                }

                $userData['role_group_id'] = $role->id;
            }

            $user->update($userData);

            if ($roleName) {
                $user->syncRoles([$roleName]);
            }

            DB::commit();

            return response()->json([
                'message' => 'User updated successfully.',
                'redirect' => route('user.index'),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'An error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {

            if (auth()->id() == $id) {
                return response()->json([
                    'message' => 'You are not allowed to delete your own account.',
                ], 403);
            }

            $table = User::findOrFail($id);
            $table->active = '0';
            $table->status = 'Not Active';
            $table->updated_by = Auth::user()->id;
            $table->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil dihapus.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function verify_user($id)
    {
        try {
            $table = User::findOrFail($id);
            $table->email_verified_at = Carbon::now();
            $table->updated_by = Auth::user()->id;
            $table->save();

            return response()->json([
                'success' => true,
                'message' => 'Pengguna berhasil diverifikasi.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function trash(Request $request)
    {
        $query = User::with(['roles', 'creator', 'updater'])->where('active', '0');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $users = $query->paginate(10)->withQueryString();
        $totalUsers = User::where('active', 1)->count();
        $totalActive = User::where('status', 'Active')->where('active', 1)->count();
        $totalVerified = User::whereNotNull('email_verified_at')->where('active', 1)->count();
        $totalLoginBulanIni = $users->filter(function ($user) {
            return Cache::has('user-is-online-'.$user->id);
        })->count();

        $x = [
            'title' => 'Deleted User List',
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => route('dashboard')],
                ['label' => 'Deleted User List', 'url' => ''],
            ],
            'totalUsers' => $totalUsers,
            'totalActive' => $totalActive,
            'totalVerified' => $totalVerified,
            'totalLoginBulanIni' => $totalLoginBulanIni,
            'users' => $users,
        ];

        return view('pengaturan.user.user_trash', $x);
    }

    public function restore_user($id)
    {
        DB::beginTransaction();

        try {
            $album = User::find($id);

            $album->active = 1;
            $album->status = 'Not Active';
            $album->updated_by = Auth::id();
            $album->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'Pengguna berhasil dikembalikan.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => true,
                'redirect' => true,
                'message' => 'User berhasil dikembalikan.',
            ]);
        }
    }
}

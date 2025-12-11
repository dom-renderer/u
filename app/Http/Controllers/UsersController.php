<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\OperationManager;
use App\Models\CorporateOffice;
use App\Models\TicketRoleUser;
use App\Models\DepartmentUser;
use App\Models\TicketMember;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\Department;
use App\Models\TicketRole;
use App\Models\UserStore;
use App\Models\UserIssue;
use App\Helpers\Helper;
use App\Models\Store;
use App\Models\User;

class UsersController extends Controller
{
    public function index(Request $request) 
    {
        $page_title = "Users";
        $page_description = "Manage users here";

        $roles = Role::latest()->get();
        $userCount = User::count();
        $archivedUserCount = User::onlyTrashed()->count();

        return view('users.index', compact('page_title','roles', 'userCount', 'archivedUserCount', 'page_description'));
    }

    public function getUsers(Request $request) {

        $roleFilter = $request->roles;
        $users = User::query()->when(!empty($roleFilter), function ($builder) use ($roleFilter) {
            $builder->whereHas('roles', function ($innerBuilder) use ($roleFilter) {
                $innerBuilder->where('name', $roleFilter);
            });
        });

        return datatables()
        ->eloquent($users)
        ->filter(function ($row) {
            if (isset(request('search')['value'])) {
                $row->where(function ($innerRow) {
                    $innerRow->where('name', 'LIKE', '%' . request()->search['value'] . '%')
                    ->orWhere('middle_name', 'LIKE', '%' . request()->search['value'] . '%')
                    ->orWhere('last_name', 'LIKE', '%' . request()->search['value'] . '%')
                    ->orWhere('phone_number', 'LIKE', '%' . request()->search['value'] . '%')
                    ->orWhere('email', 'LIKE', '%' . request()->search['value'] . '%')
                    ->orWhere('username', 'LIKE', '%' . request()->search['value'] . '%');
                });
            }
        })
        ->addColumn('currentrole', function ($row) {
            $name = $row->roles[0]->name ?? '-';

            if ($row->status) {
                return '<span class="badge bg-success"> ' . $name . ' </span>';
            } else {
                return '<span class="badge bg-danger"> ' . $name . ' </span>';
            }
        })
        ->addColumn('action', function ($row) {
            $action = '';

            if (auth()->user()->can('users.show')) {
                $action .= '<a href="'.route("users.show", $row->id).'" class="btn btn-info btn-sm me-2"> Show </a>';
            }

            if (auth()->user()->can('users.edit')) {
                $action .= '<a href="'.route('users.edit', $row->id).'" class="btn btn-warning btn-sm me-2">Edit</a>';
            }

            if (auth()->user()->can('users.destroy')) {
                $action .= '<form method="POST" action="'.route("users.destroy", $row->id).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
            }

            return $action;
        })
        ->rawColumns(['action', 'currentrole'])
        ->addIndexColumn()
        ->toJson();

    }

    public function getArchiveUsers(Request $request) {

        $roleFilter = $request->roles;
        $users = User::onlyTrashed()->when(!empty($roleFilter), function ($builder) use ($roleFilter) {
            $builder->whereHas('roles', function ($innerBuilder) use ($roleFilter) {
                $innerBuilder->where('name', $roleFilter);
            });
        });

        return datatables()
        ->eloquent($users)
        ->addColumn('currentrole', function ($row) {
            $name = $row->roles[0]->name ?? '-';

            if ($row->status) {
                return '<span class="badge bg-success"> ' . $name . ' </span>';
            } else {
                return '<span class="badge bg-danger"> ' . $name . ' </span>';
            }
        })
        ->addColumn('action', function ($row) {
            $action = '';

            if (auth()->user()->can('users.show')) {
                $action .= '<a href="'.route("users.show.deleted", $row->id).'" class="btn btn-warning btn-sm me-2"> Show </a>';
            }

            if (auth()->user()->can('users.create')) {
                $action .= '<form method="POST" action="'.route("users.restore", $row->id).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-primary btn-sm restoreGroup">Restore</button></form>';
            }

            return $action;
        })
        ->rawColumns(['action', 'currentrole'])
        ->addIndexColumn()
        ->toJson();
    }

    public function create() 
    {
        $page_title = "User Add";
        $roles = Role::all();
        $tRoles = TicketRole::get();

        return view('users.create', compact('page_title', 'roles', 'tRoles'));
    }

    public function store(StoreUserRequest $request) 
    {
        \DB::beginTransaction();

        try {
            $profile = '';

            if ($request->hasFile('profile')) {
                if (!file_exists(storage_path('app/public/users'))) {
                    mkdir(storage_path('app/public/users'), 0777, true);
                }

                $profile = 'USER-' . date('YmdHis') . uniqid() . '.' . $request->file('profile')->getClientOriginalExtension();
                $request->file('profile')->move(storage_path('app/public/users'), $profile);
            }

            $userEloquent = User::create([
                'name' => $request->name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'profile' => $profile,
                'employee_id' => $request->employee_id,
                'username' => $request->username,
                'status' => $request->status,
                'password' => $request->password
            ]);

            $userEloquent->syncRoles([$request->role]);

            if (is_iterable($request->office)) {
                if (in_array($request->role, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 1
                        ]);
                    }
                } else if (in_array($request->role, [Helper::$roles['corporate-office-manager']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 2
                        ]);
                    }
                } else if (in_array($request->role, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 3
                        ]);
                    }
                }
            }

            if (in_array($request->role, [10, 6]) && !empty($request->doms) && is_array($request->doms)) {
                foreach ($request->doms as $thisDoM) {
                    OperationManager::updateOrCreate([
                        'dom_id' => $thisDoM,
                        'ops_id' => $userEloquent->id
                    ]);
                }
            }

            $tooKeep = [];

            if (is_array($request->ticket_roles)) {
                foreach ($request->ticket_roles as $tRole) {
                    $tooKeep[] = TicketRoleUser::updateOrCreate([
                        'user_id' => $userEloquent->id,
                        'ticket_role_id' => $tRole
                    ])->id;

                    if ($tRole == 1) { //Agent
                        if (is_array($request->issues)) {
                            $toKeepIssue = [];
                            foreach ($request->issues as $thisIssue) {
                                $toKeepIssue[] = UserIssue::updateOrCreate([
                                    'user_id' => $userEloquent->id,
                                    'issue_id' => $thisIssue
                                ])->id;
                            }

                            if (!empty($toKeepIssue)) {
                                UserIssue::where('user_id', $userEloquent->id)->whereNotIn('id', $toKeepIssue)->delete();
                            } else {
                                UserIssue::where('user_id', $userEloquent->id)->delete();
                            }
                        } else {
                            UserIssue::where('user_id', $userEloquent->id)->delete();
                        }
                    }

                    if ($tRole == 2 && $userEloquent->whereHas('roles', function ($inB) {
                        $inB->whereIn('id', [
                            Helper::$roles['divisional-operations-manager'],
                            Helper::$roles['operations-manager']
                        ]);
                    })->exists()) { //DoM
                        if (is_array($request->dom_stores)) {
                            $tooKeepDom = [];

                            foreach ($request->dom_stores as $domStore) {
                                $tooKeepDom[] = $domStore;
                                Store::where('id', $domStore)->update([
                                    'dom_id' => $userEloquent->id
                                ]);
                            }

                            if (!empty($tooKeepDom)) {
                                Store::whereNotIn('id', $tooKeepDom)->where('dom_id', $userEloquent->id)->update([
                                    'dom_id' => null
                                ]);
                            } else {
                                Store::where('dom_id', $userEloquent->id)->update([
                                    'dom_id' => null
                                ]);
                            }
                        } else {
                            Store::where('dom_id', $userEloquent->id)->update([
                                'dom_id' => null
                            ]);
                        }
                    }
                }

                if (!empty($tooKeep)) {
                    TicketRoleUser::where('user_id', $userEloquent->id)->whereNotIn('id', $tooKeep)->delete();
                } else {
                    TicketRoleUser::where('user_id', $userEloquent->id)->delete();
                }
            } else {
                TicketRoleUser::where('user_id', $userEloquent->id)->delete();
            }

            \DB::commit();
            return redirect()->route('users.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error( 'User creation error ' . $e->getMessage() . ' on line ' . $e->getLine());
            return redirect()->route('users.index')->with('error', 'Something went wrong');
        }
    }

    public function show(User $user) 
    {
        $page_title = 'User show';

        $type = '';
        $store = [];

        if(in_array($user->roles[0]->id, [2, 3, 4, 11])) {
            $storeNames = Store::select('id', 'name')->withTrashed()->whereIn('id', $user->destore->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->destore as $item) {
                $store[$item->type_id] = isset($storeNames[$item->type_id]) ? $storeNames[$item->type_id] : '';
            }
            $type = 'Store';
        } else if (in_array($user->roles[0]->id, [5])) {
            $officeNames = CorporateOffice::select('id', 'name')->withTrashed()->whereIn('id', $user->deoffice->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->deoffice as $item) {
                $store[$item->type_id] = isset($officeNames[$item->type_id]) ? $officeNames[$item->type_id] : '';
            }
            $type = 'Office';
        } else if (in_array($user->roles[0]->id, [6, 7, 10])) {
            $departmentName = Department::select('id', 'name')->withTrashed()->whereIn('id', $user->dedepartment->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->dedepartment as $item) {
                $store[$item->type_id] = isset($departmentName[$item->type_id]) ? $departmentName[$item->type_id] : '';
            }
            $type = 'Department';
        }

        return view('users.show', ['user' => $user, 'page_title' => $page_title , 'store' => $store, 'type' => $type]);
    }

    public function edit(User $user) 
    {        
        $page_title = "User Edit";
        $roles = Role::all();
        $tRoles = TicketRole::get();

        $store = $selectedDoms = [];

        if(in_array($user->roles[0]->id, [2, 3, 4, 11])) {
            $storeNames = Store::select('id', 'name')->withTrashed()->whereIn('id', $user->destore->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->destore as $item) {
                $store[$item->type_id] = isset($storeNames[$item->type_id]) ? $storeNames[$item->type_id] : '';
            }
        } else if (in_array($user->roles[0]->id, [5])) {
            $officeNames = CorporateOffice::select('id', 'name')->withTrashed()->whereIn('id', $user->deoffice->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->deoffice as $item) {
                $store[$item->type_id] = isset($officeNames[$item->type_id]) ? $officeNames[$item->type_id] : '';
            }
        } else if (in_array($user->roles[0]->id, [6, 7, 10])) {
            $departmentName = Department::select('id', 'name')->withTrashed()->whereIn('id', $user->dedepartment->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->dedepartment as $item) {
                $store[$item->type_id] = isset($departmentName[$item->type_id]) ? $departmentName[$item->type_id] : '';
            }
        }

        if (in_array($user->roles[0]->id, [10, 6])) {
            $selectedDoms = OperationManager::with('dom')->select('id', 'dom_id')->where('ops_id', $user->id)->get()->toArray();
        }

        return view('users.edit', compact('user', 'roles', 'page_title', 'store', 'tRoles', 'selectedDoms'));
    }

    public function update(UpdateUserRequest $request) 
    {

        \DB::beginTransaction();

        try {
            $userEloquent = User::find($request->id);
        
            $userEloquent->name = $request->name;
            $userEloquent->middle_name = $request->middle_name;
            $userEloquent->last_name = $request->last_name;
            $userEloquent->email = $request->email;
            $userEloquent->username = $request->username;
            $userEloquent->phone_number = $request->phone_number;
            $userEloquent->status = $request->status;
            $userEloquent->employee_id = $request->employee_id;

            $profile = $userEloquent->profile;

            if ($request->hasFile('profile')) {
                if (!file_exists(storage_path('app/public/users'))) {
                    mkdir(storage_path('app/public/users'), 0777, true);
                }
    
                $profile = 'USER-' . date('YmdHis') . uniqid() . '.' . $request->file('profile')->getClientOriginalExtension();
                $request->file('profile')->move(storage_path('app/public/users'), $profile);


                if (file_exists(storage_path("app/public/users/{$profile}")) && file_exists(storage_path("app/public/users/{$userEloquent->profile}"))) {
                    unlink(storage_path("app/public/users/{$userEloquent->profile}"));
                }

                $userEloquent->profile = $profile;
            }
    
            if (!empty($request->password)) {
                $userEloquent->password = $request->password;
            }
    
            $userEloquent->save();
            $userEloquent->syncRoles([$request->role]);
            Designation::where('user_id', $userEloquent->id)->delete();

            $domsToKeep = [];

            if (in_array($request->role, [10, 6]) && !empty($request->doms) && is_array($request->doms)) {
                foreach ($request->doms as $thisDoM) {
                    $domsToKeep[] = OperationManager::updateOrCreate([
                        'dom_id' => $thisDoM,
                        'ops_id' => $userEloquent->id
                    ])->id;
                }
            }

            if (!empty($domsToKeep)) {
                OperationManager::where('ops_id', $userEloquent->id)->whereNotIn('id', $domsToKeep)->delete();
            } else {
                OperationManager::where('ops_id', $userEloquent->id)->delete();
            }

            if (is_iterable($request->office)) {
                if (in_array($request->role, [Helper::$roles['store-phone'], Helper::$roles['store-manager'], Helper::$roles['store-employee'], Helper::$roles['store-cashier']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 1
                        ]);
                    }
                } else if (in_array($request->role, [Helper::$roles['corporate-office-manager']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 2
                        ]);
                    }
                } else if (in_array($request->role, [Helper::$roles['divisional-operations-manager'], Helper::$roles['head-of-department'], Helper::$roles['operations-manager']])) {
                    foreach ($request->office as $office) {
                        Designation::create([
                            'user_id' => $userEloquent->id,
                            'type_id' => $office,
                            'type' => 3
                        ]);
                    }
                }
            }

            $tooKeep = [];

            if (is_array($request->ticket_roles)) {
                foreach ($request->ticket_roles as $tRole) {
                    $tooKeep[] = TicketRoleUser::updateOrCreate([
                        'user_id' => $userEloquent->id,
                        'ticket_role_id' => $tRole
                    ])->id;

                    if ($tRole == 1) { //Agent
                        if (is_array($request->issues)) {
                            $toKeepIssue = [];
                            foreach ($request->issues as $thisIssue) {
                                $toKeepIssue[] = UserIssue::updateOrCreate([
                                    'user_id' => $userEloquent->id,
                                    'issue_id' => $thisIssue
                                ])->id;
                            }

                            if (!empty($toKeepIssue)) {
                                UserIssue::where('user_id', $userEloquent->id)->whereNotIn('id', $toKeepIssue)->delete();
                            } else {
                                UserIssue::where('user_id', $userEloquent->id)->delete();
                            }
                        } else {
                            UserIssue::where('user_id', $userEloquent->id)->delete();
                        }
                    }

                    if ($tRole == 2 && $userEloquent->whereHas('roles', function ($inB) {
                        $inB->whereIn('id', [
                            Helper::$roles['divisional-operations-manager'],
                            Helper::$roles['operations-manager']
                        ]);
                    })->exists()) { //DoM
                        if (is_array($request->dom_stores)) {
                            $tooKeepDom = [];

                            foreach ($request->dom_stores as $domStore) {
                                $tooKeepDom[] = $domStore;
                                Store::where('id', $domStore)->update([
                                    'dom_id' => $userEloquent->id
                                ]);
                            }

                            if (!empty($tooKeepDom)) {
                                Store::whereNotIn('id', $tooKeepDom)->where('dom_id', $userEloquent->id)->update([
                                    'dom_id' => null
                                ]);
                            } else {
                                Store::where('dom_id', $userEloquent->id)->update([
                                    'dom_id' => null
                                ]);
                            }
                        } else {
                            Store::where('dom_id', $userEloquent->id)->update([
                                'dom_id' => null
                            ]);
                        }
                    }
                }

                if (!empty($tooKeep)) {
                    TicketRoleUser::where('user_id', $userEloquent->id)->whereNotIn('id', $tooKeep)->delete();
                } else {
                    TicketRoleUser::where('user_id', $userEloquent->id)->delete();
                }
            } else {
                TicketRoleUser::where('user_id', $userEloquent->id)->delete();
            }            

            \DB::commit();
            return redirect()->route('users.index')->with('success', 'User updated successfully');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error( 'User updation error ' . $e->getMessage() . ' on line ' . $e->getLine());
            return redirect()->route('users.index')->with('error', 'Something went wrong');
        }
    }

    
    public function showDeleted($id) 
    {
        $user = User::withTrashed()->find($id);

        $store = [];
        $type = '';

        if(in_array($user->roles[0]->id, [2, 3, 4, 11])) {
            $storeNames = Store::select('id', 'name')->withTrashed()->whereIn('id', $user->destore->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->destore as $item) {
                $store[$item->type_id] = isset($storeNames[$item->type_id]) ? $storeNames[$item->type_id] : '';
            }
            $type = 'Store';
        } else if (in_array($user->roles[0]->id, [5])) {
            $officeNames = CorporateOffice::select('id', 'name')->withTrashed()->whereIn('id', $user->deoffice->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->deoffice as $item) {
                $store[$item->type_id] = isset($officeNames[$item->type_id]) ? $officeNames[$item->type_id] : '';
            }
            $type = 'Office';
        } else if (in_array($user->roles[0]->id, [6, 7, 10])) {
            $departmentName = Department::select('id', 'name')->withTrashed()->whereIn('id', $user->dedepartment->pluck('type_id'))->pluck('name', 'id')->toArray();

            foreach ($user->dedepartment as $item) {
                $store[$item->type_id] = isset($departmentName[$item->type_id]) ? $departmentName[$item->type_id] : '';
            }
            $type = 'Department';
        }

        return view('users.show', [
            'user' => $user,
            'store' => $store,
            'type' => $type
        ]);
    }


    public function destroy(User $user) 
    {
        DepartmentUser::where('user_id', $user->id)->delete();
        TicketMember::where('user_id', $user->id)->delete();
        $user->delete();
        
        return redirect()->route('users.index')->with('success', __('User archived successfully.'));
    }

   
    public function restore($id) 
    {
        $user = User::onlyTrashed()->find($id);
        $user->restore();
        
        return redirect()->route('users.index')->with('success', __(' User unable to restore or already restored.'));

    }
    
    public function remove($id) 
    {
        return redirect()->route('users.index')->withSuccess(__('Unable to deleted User.'));
    }

    public static function showChangePasswordForm(){
        return view('auth.changepassword');
    }

    public static function changePassword(Request $request){
        if (!(Hash::check($request->get('current-password'), request()->user()->password))) {
            return redirect()->back()->withErrors("Your current password does not matches with the password you provided. Please try again.");
        }
    
        if(strcmp($request->get('current-password'), $request->get('new-password')) == 0){
            return redirect()->back()->withErrors("New Password cannot be same as your current password. Please choose a different password.");
        }
    
        $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:8|required_with:new-password_confirmation',
        ]);
    
        //Change Password
        $user = request()->user();
        $user->password = $request->get('new-password');
        $user->password_change_at = \Carbon\Carbon::now();
        $user->save();
    
        return redirect()->route('dashboard.index')->withSuccess("Password changed successfully !");
    
    }

    public static function getAllUsers(Request $request) {
        $queryString = trim($request->searchQuery);
        $page = $request->input('page', 1);
        $limit = 10;
        $roles = array_filter(explode(',', $request->roles));
        $branchType = $request->branchType;
        $branchId = $request->branchId;
        $getAll = $request->getall;
        $ignoreDesignation = $request->ignoreDesignation;
        
        $query = User::query();

        if (auth()->user()->isAdmin()) {
            if (is_numeric($branchId) && $branchId > 0) {
                if ($branchType == 1) {

                    $allStoresUsers = Designation::where('type_id', $branchId)->where('type', 1)->pluck('user_id')->toArray();
                    if (!empty($allStoresUsers)) {
                        $query->whereIn('id', $allStoresUsers);
                    } else {
                        $query->whereIn('id', [0]);
                    }

                } else if ($branchType == 2) {

                    $allCorporateOfficeUsers = Designation::where('type_id', $branchId)->where('type', 2)->pluck('user_id')->toArray();
                    if (!empty($allCorporateOfficeUsers)) {
                        $query->whereIn('id', $allCorporateOfficeUsers);
                    } else {
                        $query->whereIn('id', [0]);
                    }

                } else if ($branchType == 3) {

                    $allDepartmentUsers = Designation::where('type_id', $branchId)->where('type', 3)->pluck('user_id')->toArray();
                    if (!empty($allDepartmentUsers)) {
                        $query->whereIn('id', $allDepartmentUsers);
                    } else {
                        $query->whereIn('id', [0]);
                    }

                } else {
                    $query->whereIn('id', [0]);
                }
            } else if (!empty($roles)) {
                $query = $query->when(!empty($roles), function ($builder) use ($roles) {
                    return $builder->whereHas('roles', function ($innerBuilder) use ($roles) {
                        return $innerBuilder->whereIn('id', $roles);
                    });
                });
            } else {
                if ($ignoreDesignation != 1) {
                    $query->whereIn('id', [0]);
                }
            }
        
            if (!empty($queryString)) {
                $query->where(function ($innerB) use ($queryString) {
                    $innerB->where('name', 'LIKE', "%{$queryString}%")
                    ->orWhere('middle_name', 'LIKE', "%{$queryString}%")
                    ->orWhere('last_name', 'LIKE', "%{$queryString}%")
                    ->orWhere('phone_number', 'LIKE', "%{$queryString}%")
                    ->orWhere('employee_id', 'LIKE', "%{$queryString}%");
                });
            }
        
            if (!empty($request->department) && is_numeric($request->department) && $request->department > 0) {
                $query->whereHas('depuser', function ($innerBuilder) {
                    $innerBuilder->where('department_id', request('department'));
                });
            }
        } else {
            if (!empty($queryString)) {
                $query->where(function ($innerB) use ($queryString) {
                    $innerB->where('name', 'LIKE', "%{$queryString}%")
                    ->orWhere('middle_name', 'LIKE', "%{$queryString}%")
                    ->orWhere('last_name', 'LIKE', "%{$queryString}%")
                    ->orWhere('phone_number', 'LIKE', "%{$queryString}%")
                    ->orWhere('employee_id', 'LIKE', "%{$queryString}%");
                });
            }

            // $query->where('id', auth()->user()->id);
        }

        $data = $query->paginate($limit, ['*'], 'page', $page);
        $response = $data->map(function ($pro) {
            return [
                'id' => $pro->id,
                'text' => (!empty($pro->employee_id) ? "{$pro->employee_id} - " : '') . "{$pro->name} {$pro->middle_name} {$pro->last_name}"
            ];
        });

        if ($getAll && $page == 1 && auth()->user()->isAdmin()) {
            $response->push(['id' => 'all', 'text' => 'All']);
        }

        return response()->json([
            'items' => $response->reverse()->values(),
            'pagination' => [
                'more' => $data->hasMorePages()
            ]
        ]);
    }

    public function import(Request $request) {
        $response = $leaveBlank = [];
        $errorCount = $successCount = 0;

        $file = $request->file('xlsx');
        $type = $file->getClientOriginalExtension();

        if (!in_array($type, ['xlsx'])) {

            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'File is not supported. please upload xlsx.'
                ]
            ], $file);

            return response()->json(['status' => false, 'message' => 'File is not supported. please upload xlsx.']);
        }

        $expectedHeaders = [
            'first name',
            'middle name',
            'last name',
            'email',
            'employee id',
            'username',
            'phone number',
            'status',
            'password',
            'role'
        ];

        $isFileValid = false;
        $data = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\LocationImport(), $file);

        if (!isset($data[0][0][0])) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'Uploaded file headers do not match the expected format.'
                ]
            ], $file);
            return response()->json(['status' => false, 'message' => 'Uploaded file headers do not match the expected format.']);
        }

        if (
            strtolower($data[0][0][0]) == $expectedHeaders[0] &&
            strtolower($data[0][0][1]) == $expectedHeaders[1] &&
            strtolower($data[0][0][2]) == $expectedHeaders[2] &&
            strtolower($data[0][0][3]) == $expectedHeaders[3] &&
            strtolower($data[0][0][4]) == $expectedHeaders[4] &&
            strtolower($data[0][0][5]) == $expectedHeaders[5] &&
            strtolower($data[0][0][6]) == $expectedHeaders[6] &&
            strtolower($data[0][0][7]) == $expectedHeaders[7] &&
            strtolower($data[0][0][8]) == $expectedHeaders[8] &&
            strtolower($data[0][0][9]) == $expectedHeaders[9]
        ) {
            $isFileValid = true;
        }

        if (!$isFileValid) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'Uploaded file headers do not match the expected format.'
                ]
            ], $file);
            return response()->json(['status' => false, 'message' => 'Uploaded file headers do not match the expected format.']);
        }

        $data = array_splice($data[0], 1, count($data[0]));

        $allEmployeeIdFromSheet = array_column($data, 4);
        $allUsernameFromSheet = array_column($data, 5);
        $allPhoneNumberFromSheet = array_column($data, 6);

        $allEmployeeIdFromSheetUniqe = $allEmployeeIdFromSheet;
        $allUsernameFromSheetUniqe = $allUsernameFromSheet;
        $allPhoneNumberFromSheetUniqe = $allPhoneNumberFromSheet;

        if (count($allEmployeeIdFromSheet) != count($allEmployeeIdFromSheetUniqe)) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => count($allEmployeeIdFromSheet) - count($allEmployeeIdFromSheetUniqe),
                'status' => 2,
                'response' => [
                    'Employee ID exists for multiple records in sheet.'
                ]
            ], $file);

            \DB::commit();
            return response()->json(['status' => false, 'message' => 'Employee ID exists for multiple records in sheet.']);
        }

        if (count($allUsernameFromSheet) != count($allUsernameFromSheetUniqe)) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => count($allUsernameFromSheet) - count($allUsernameFromSheetUniqe),
                'status' => 2,
                'response' => [
                    'Username exists for multiple records in sheet.'
                ]
            ], $file);

            \DB::commit();
            return response()->json(['status' => false, 'message' => 'Username exists for multiple records in sheet.']);
        }

        if (count($allPhoneNumberFromSheet) != count($allPhoneNumberFromSheetUniqe)) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => count($allPhoneNumberFromSheet) - count($allPhoneNumberFromSheetUniqe),
                'status' => 2,
                'response' => [
                    'Phone Number exists for multiple records in sheet.'
                ]
            ], $file);

            \DB::commit();
            return response()->json(['status' => false, 'message' => 'Phone Number exists for multiple records in sheet.']);
        }

        if (empty($data)) {
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => 0,
                'error' => 0,
                'status' => 2,
                'response' => [
                    'File has not data.'
                ]
            ], $file);

            return response()->json(['status' => false, 'message' => 'File has not data']);
        }

        $helperRoles = Helper::$roles;
        $rolesOtherThanSystem = Role::whereNotIn('id', $helperRoles)
        ->pluck('id', 'name')
        ->mapWithKeys(fn($id, $name) => [\Illuminate\Support\Str::slug($name) => $id])
        ->toArray();

        if (!empty($rolesOtherThanSystem)) {
            $helperRoles = array_merge($helperRoles, $rolesOtherThanSystem);
        }

        $allRoles = array_keys($helperRoles);
        $allRoles = array_combine($allRoles, $allRoles);

        \DB::beginTransaction();

        try {

            foreach ($data as $key => $row) {
                if (empty(trim($row[3]))) {
                    $errorCount++;
                    $response[$key] = 'Email is required at D' . ($key + 1);
                    continue;
                }

                if (empty(trim($row[5]))) {
                    $errorCount++;
                    $response[$key] = 'Username is required at F' . ($key + 1);
                    continue;
                }

                if (empty(trim($row[6]))) {
                    $errorCount++;
                    $response[$key] = 'Phone Number is required at G' . ($key + 1);
                    continue;
                }

                if (!isset($allRoles[$row[9]])) {
                    $errorCount++;
                    $response[$key] = 'Role does not exists at J' . ($key + 1);
                    continue;
                }                

                $allEmployeeId = User::withTrashed()->pluck('employee_id', 'employee_id')->toArray();
                $allUsernames = User::withTrashed()->pluck('username', 'username')->toArray();
                $allPhoneNumbers = User::withTrashed()->pluck('phone_number', 'phone_number')->toArray();

                //set true because for now all users will have employee_id
                if (true) {

                    if (empty(trim($row[4]))) {
                        $errorCount++;
                        $response[$key] = 'Employee ID is required at E' . ($key + 1);
                        continue;
                    }

                    if (in_array($row[4], $allEmployeeId)) {
                        /***
                         * Update User
                         * **/

                        if (User::withTrashed()->where('employee_id', '!=', $row[4])->where('username', $row[5])->exists()) {
                            $errorCount++;
                            $response[$key] = 'Use different username at F' . ($key + 1);
                            continue;
                        }

                        if (User::withTrashed()->where('employee_id', '!=', $row[4])->where('phone_number', $row[6])->exists()) {
                            $errorCount++;
                            $response[$key] = 'Use different phone number at G' . ($key + 1);
                            continue;
                        }

                        $currentUserRole = User::withTrashed()->where(function ($builder) use ($row) {
                            $builder->where('employee_id', $row[4]);
                        })->first()->roles()->pluck('id')->toArray();

                        $userEloquent = User::withTrashed()->where(function ($builder) use ($row) {
                            $builder->where('employee_id', $row[4]);
                        })->first();

                        $userEloquent->name = $row[0];
                        $userEloquent->middle_name = $row[1];
                        $userEloquent->last_name = $row[2];
                        $userEloquent->email = $row[3];
                        $userEloquent->employee_id = $row[4];
                        $userEloquent->username = $row[5];
                        $userEloquent->phone_number = $row[6];
                        $userEloquent->deleted_at = null;
                        $userEloquent->status = strtolower($row[7]) == 'enable' ? 1 : 0;

                        if (!empty(trim($row[8]))) {
                            $userEloquent->password = trim($row[8]);
                        }

                        $userEloquent->save();
                        $successCount++;

                        if (isset($currentUserRole[0])) {
                            if (!in_array($helperRoles[$row[9]], $currentUserRole)) {
                                /***
                                 * Role Updated
                                 * **/

                                $offices = [];
                                $theTypeId = 3;

                                if ($row[9] == 'divisional-operations-manager' || $row[9] == 'operations-manager' || $row[9] == 'head-of-department') {
                                    $includedOfficeData = false;

                                    if (isset($row[10])) {
                                        $offices = explode(',', strtolower(str_replace(" ", '', $row[10])));
                                        if (!empty($offices)) {
                                            $includedOfficeData = true;
                                            $offices = Department::select('id')->whereIn(\DB::raw('LOWER(name)'), $offices)->pluck('id')->toArray();
                                        }
                                    }

                                    if ($includedOfficeData == false) {
                                        if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->exists()) {
                                            $offices = Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->pluck('type_id')->toArray();
                                        } else if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->doesntExist()) {
                                            $offices = [Department::select('id')->first()->id];
                                        }
                                    }
                                } else if ($row[9] == 'store-phone' || $row[9] == 'store-manager' || $row[9] == 'store-employee' || $row[9] == 'store-cashier') {
                                    $theTypeId = 1;
                                    $includedOfficeData = false;

                                    if (isset($row[10])) {
                                        $offices = explode(',', str_replace(" ", '', $row[10]));
                                        if (!empty($offices)) {
                                            $includedOfficeData = true;
                                            $offices = Store::select('id')->whereIn('code', $offices)->pluck('id')->toArray();
                                        }
                                    }

                                    if ($includedOfficeData == false) {
                                        if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->exists()) {
                                            $offices = Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->pluck('type_id')->toArray();
                                        } else if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->doesntExist()) {
                                            $offices = [Store::select('id')->first()->id];
                                        }
                                    }                                    
                                }

                                $userEloquent->syncRoles([$helperRoles[$row[9]]]);
                                Designation::where('user_id', $userEloquent->id)->delete();

                                if (!empty($offices) && in_array($helperRoles[$row[9]], [$helperRoles['store-phone'], $helperRoles['store-manager'], $helperRoles['store-employee'], $helperRoles['store-cashier'], $helperRoles['divisional-operations-manager'], $helperRoles['head-of-department'], $helperRoles['operations-manager']])) {
                                    foreach ($offices as $office) {
                                        Designation::create([
                                            'user_id' => $userEloquent->id,
                                            'type_id' => $office,
                                            'type' => $theTypeId
                                        ]);
                                    }
                                }

                                /***
                                 * Role Updated
                                 * **/
                            } else {

                                $offices = [];
                                $theTypeId = 3;

                                if ($row[9] == 'divisional-operations-manager' || $row[9] == 'head-of-department' || $row[9] == 'operations-manager') {
                                    $includedOfficeData = false;

                                    if (isset($row[10])) {
                                        $offices = explode(',', strtolower(str_replace(" ", '', $row[10])));
                                        if (!empty($offices)) {
                                            $includedOfficeData = true;
                                            $offices = Department::select('id')->whereIn(\DB::raw('LOWER(name)'), $offices)->pluck('id')->toArray();
                                        }
                                    }

                                    if ($includedOfficeData == false) {
                                        if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->exists()) {
                                            $offices = Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->pluck('type_id')->toArray();
                                        } else if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->doesntExist()) {
                                            $offices = [Department::select('id')->first()->id];
                                        }
                                    }

                                } else if ($row[9] == 'store-phone' || $row[9] == 'store-manager' || $row[9] == 'store-employee' || $row[9] == 'store-cashier') {
                                    $theTypeId = 1;
                                    $includedOfficeData = false;

                                    if (isset($row[10])) {
                                        $offices = explode(',', str_replace(" ", '', $row[10]));
                                        if (!empty($offices)) {
                                            $includedOfficeData = true;
                                            $offices = Store::select('id')->whereIn('code', $offices)->pluck('id')->toArray();
                                        }
                                    }

                                    if ($includedOfficeData == false) {
                                        if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->exists()) {
                                            $offices = Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->pluck('type_id')->toArray();
                                        } else if (Designation::where('user_id', $userEloquent->id)->where('type', $theTypeId)->doesntExist()) {
                                            $offices = [Store::select('id')->first()->id];
                                        }
                                    }
                                }

                                Designation::where('user_id', $userEloquent->id)->delete();

                                if (in_array($helperRoles[$row[9]], [$helperRoles['store-phone'], $helperRoles['store-manager'], $helperRoles['store-employee'], $helperRoles['store-cashier'], $helperRoles['divisional-operations-manager'], $helperRoles['head-of-department'], $helperRoles['operations-manager']])) {
                                    foreach ($offices as $office) {
                                        Designation::create([
                                            'user_id' => $userEloquent->id,
                                            'type_id' => $office,
                                            'type' => $theTypeId
                                        ]);
                                    }
                                }

                            }
                        }

                        /***
                         * Update User
                         * **/
                    } else {
                        /***
                         * Add User
                         * **/

                    if (in_array($row[5], $allUsernames)) {
                        $errorCount++;
                        $response[$key] = 'Use different username at F' . ($key + 1);
                        continue;
                    }

                    if (in_array($row[6], $allPhoneNumbers)) {
                        $errorCount++;
                        $response[$key] = 'Use different phone number at G' . ($key + 1);
                        continue;
                    }

                        $userEloquent = new User();
                        $userEloquent->name = $row[0];
                        $userEloquent->middle_name = $row[1];
                        $userEloquent->last_name = $row[2];
                        $userEloquent->email = $row[3];
                        $userEloquent->employee_id = $row[4];
                        $userEloquent->username = $row[5];
                        $userEloquent->phone_number = $row[6];
                        $userEloquent->status = strtolower($row[7]) == 'enable' ? 1 : 0;

                        if (!empty(trim($row[8]))) {
                            $userEloquent->password = trim($row[8]);
                        } else {
                            $userEloquent->password = $row[6];
                        }

                        $userEloquent->save();
                        $userEloquent->syncRoles([$helperRoles[$row[9]]]);

                        $successCount++;

                        $offices = [];
                        $theTypeId = 3;

                        if ($row[9] == 'divisional-operations-manager' || $row[9] == 'operations-manager' || $row[9] == 'head-of-department') {
                            if (isset($row[10])) {
                                $offices = explode(',', strtolower(str_replace(" ", '', $row[10])));
                                if (!empty($offices)) {
                                    $offices = Department::select('id')->whereIn(\DB::raw('LOWER(name)'), $offices)->pluck('id')->toArray();
                                }
                            }
                        } else if ($row[9] == 'store-phone' || $row[9] == 'store-manager' || $row[9] == 'store-employee' || $row[9] == 'store-cashier') {
                            $theTypeId = 1;
                            if (isset($row[10])) {
                                $offices = explode(',', str_replace(" ", '', $row[10]));
                                if (!empty($offices)) {
                                    $offices = Store::select('id')->whereIn('code', $offices)->pluck('id')->toArray();
                                }
                            }
                        }

                        if (!empty($offices) && in_array($helperRoles[$row[9]], [$helperRoles['store-phone'], $helperRoles['store-manager'], $helperRoles['store-employee'], $helperRoles['store-cashier'], $helperRoles['divisional-operations-manager'], $helperRoles['operations-manager'], $helperRoles['head-of-department']])) {
                            foreach ($offices as $office) {
                                Designation::create([
                                    'user_id' => $userEloquent->id,
                                    'type_id' => $office,
                                    'type' => $theTypeId
                                ]);
                            }
                        }

                        array_push($allEmployeeId, [$row[4] => $row[4]]);
                        array_push($allUsernames, [$row[5] => $row[5]]);
                        array_push($allPhoneNumbers, [$row[6] => $row[6]]);

                        $allEmployeeId = array_filter($allEmployeeId);
                        $allUsernames = array_filter($allUsernames);
                        $allPhoneNumbers = array_filter($allPhoneNumbers);                        

                        /***
                         * Add User
                         * **/
                    }
                } else {
                    if (in_array($row[6], $allPhoneNumbers)) {
                        /***
                         * Update User
                         * **/

                        if (User::withTrashed()->where('phone_number', '!=', $row[6])->where('username', $row[5])->exists()) {
                            $errorCount++;
                            $response[$key] = 'Use different username at F' . ($key + 1);
                            continue;
                        }

                        $currentUserRole = User::withTrashed()->where(function ($builder) use ($row) {
                            $builder->where('phone_number', $row[6]);
                        })->first()->roles()->pluck('id')->toArray();

                        $userEloquent = User::withTrashed()->where(function ($builder) use ($row) {
                            $builder->where('phone_number', $row[6]);
                        })->first();

                        $userEloquent->name = $row[0];
                        $userEloquent->middle_name = $row[1];
                        $userEloquent->last_name = $row[2];
                        $userEloquent->email = $row[3];
                        $userEloquent->employee_id = $row[4];
                        $userEloquent->username = $row[5];
                        $userEloquent->phone_number = $row[6];
                        $userEloquent->deleted_at = null;
                        $userEloquent->status = strtolower($row[7]) == 'enable' ? 1 : 0;

                        if (!empty(trim($row[8]))) {
                            $userEloquent->password = trim($row[8]);
                        }

                        $userEloquent->save();
                        $successCount++;

                        if (isset($currentUserRole[0])) {
                            if (!in_array($helperRoles[$row[9]], $currentUserRole)) {
                                /***
                                 * Role Updated
                                 * **/

                                $userEloquent->syncRoles([$helperRoles[$row[9]]]);
                                Designation::where('user_id', $userEloquent->id)->delete();

                                /***
                                 * Role Updated
                                 * **/
                            }
                        }

                        /***
                         * Update User
                         * **/
                    } else {
                        /***
                         * Add User
                         * **/

                    if (in_array($row[5], $allUsernames)) {
                        $errorCount++;
                        $response[$key] = 'Use different username at F' . ($key + 1);
                        continue;
                    }

                    if (in_array($row[6], $allPhoneNumbers)) {
                        $errorCount++;
                        $response[$key] = 'Use different phone number at G' . ($key + 1);
                        continue;
                    }

                        $userEloquent = new User();
                        $userEloquent->name = $row[0];
                        $userEloquent->middle_name = $row[1];
                        $userEloquent->last_name = $row[2];
                        $userEloquent->email = $row[3];
                        $userEloquent->employee_id = $row[4];
                        $userEloquent->username = $row[5];
                        $userEloquent->phone_number = $row[6];
                        $userEloquent->status = strtolower($row[7]) == 'enable' ? 1 : 0;

                        if (!empty(trim($row[8]))) {
                            $userEloquent->password = trim($row[8]);
                        }

                        $userEloquent->save();
                        $userEloquent->syncRoles([$helperRoles[$row[9]]]);

                        $successCount++;

                        array_push($allEmployeeId, [$row[4] => $row[4]]);
                        array_push($allUsernames, [$row[5] => $row[5]]);
                        array_push($allPhoneNumbers, [$row[6] => $row[6]]);

                        $allEmployeeId = array_filter($allEmployeeId);
                        $allUsernames = array_filter($allUsernames);
                        $allPhoneNumbers = array_filter($allPhoneNumbers);

                        /***
                         * Add User
                         * **/
                    }
                }
            }
            
            \App\Http\Controllers\ChecklistSchedulingController::recordImport([
                'checklist_id' => null,
                'type' => 1,
                'file_name' => $file->getClientOriginalName(),
                'success' => $successCount,
                'error' => $errorCount,
                'status' => $successCount == 0 ? 2 : (
                    $errorCount > 0 ? 3 : 1
                ),
                'response' => $response,
                'leave_blank' => $leaveBlank
            ], $file, true);
            
            

            \DB::commit();
            return response()->json(['status' => true, 'message' => 'Users list updated successfully.']);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('ERROR ON USER IMPORT:' . $e->getMessage() . ' ON LINE ' . $e->getLine());
            return response()->json(['status' => false, 'message' => 'Something went wrong.']);
        }
    }

    public function export(Request $request) {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\UsersExport, 'users.xlsx');
    }
}
<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{

    public function index(Request $request)
    {   
        if ($request->ajax()) {

            return datatables()
            ->eloquent(Role::query())
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('roles.show')) {
                    $action .= '<a href="'.route("roles.show", $row->id).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('roles.edit')) {
                    $action .= '<a href="'.route('roles.edit', $row->id).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('roles.destroy')) {
                    if (!in_array($row->id, range(1, 12))) {
                        if (!in_array($row->id, auth()->user()->roles->pluck('id')->toArray())) {
                            $action .= '<form method="POST" action="'.route("roles.destroy", $row->id).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                        }
                    }
                }

                return $action;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->toJson();
        }

        $page_title = 'Roles';
        $page_description = 'Manager roles here';
        return view('roles.index',compact('page_title', 'page_description'));
    }

    public function create()
    {
        $permissions = Permission::get();
        $page_title = 'Role Add';

        return view('roles.create', compact('permissions', 'page_title'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', function ($name, $value, $fail){
                if (Role::where(\DB::raw('LOWER(name)'), strtolower($value))->exists()) {
                    $fail("Role with this name is already exists.");
                }
        }],
            'permission' => 'required',
        ]);
    
        $role = Role::create(['name' => $request->get('name')]);
        $role->syncPermissions($request->get('permission'));
    
        return redirect()->route('roles.index')->with('success','Role created successfully');
    }

    public function show(Role $role)
    {
        $rolePermissions = $role->permissions;
        $page_title = 'Role Show';
    
        return view('roles.show', compact('role', 'rolePermissions', 'page_title'));
    }

    public function edit(Role $role)
    {
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $permissions = Permission::get();
        $page_title = 'Role Edit';
    
        return view('roles.edit', compact('role', 'rolePermissions', 'permissions', 'page_title'));
    }
    
    public function update(Role $role, Request $request)
    {
        $roleId = $role->id;

        $request->validate([
            'name' => ['required', function ($name, $value, $fail) use ($roleId) {
                if (Role::where('id', '!=', $roleId)->where(\DB::raw('LOWER(name)'), strtolower($value))->exists()) {
                    $fail("Role with this name is already exists.");
                }
            }],
            'permission' => 'required',
        ]);
        
        $role->update($request->only('name'));
    
        $role->syncPermissions($request->get('permission'));
    
        return redirect()->route('roles.index')->with('success','Role updated successfully');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->id, range(1, 10))) {
            return redirect()->route('roles.index')->with('error', "Can't delete {$role->name} role because it is a system defined.");
        }

        $role->syncPermissions([]);
        $role->delete();
        
        return redirect()->route('roles.index')->with('success','Role deleted successfully');
    }
}
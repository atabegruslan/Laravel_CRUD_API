<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Session;

class RoleController extends Controller
{
    private $feature = 'role';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = Role::all();
        $data  = ['roles' => $roles, 'feature' => $this->feature];

        return view('role.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::all();
        
        $data = [
            'role'                  => null, 
            'permissions'           => $permissions,
            'selectedPermissions'   => [],
            'selectedPermissionIds' => [],
            'feature'               => $this->feature,
        ];

        return view('role.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]); 

        $role       = new Role;
        $role->name = $request->input('name');
        $role->save();

        $permissions = $request->input('permission_ids');
        $role->syncPermissions($permissions);

        Session::flash('success', 'Role Created');

        return redirect('role');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role                = Role::findOrFail($id);
        $selectedPermissions = $role->permissions()->get();

        $data = [
            'role'                  => $role, 
            'permissions'           => [],
            'selectedPermissions'   => $selectedPermissions,
            'selectedPermissionIds' => [],
            'feature'               => $this->feature,
        ];

        return view('role.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role                  = Role::findOrFail($id);
        $permissions           = Permission::all();
        $selectedPermissions   = $role->permissions()->get();
        $selectedPermissionIds = $selectedPermissions->pluck('id')->toArray();

        $data = [
            'role'                  => $role, 
            'permissions'           => $permissions,
            'selectedPermissions'   => $selectedPermissions,
            'selectedPermissionIds' => $selectedPermissionIds,
            'feature'               => $this->feature,
        ];

        return view('role.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
        ]); 

        $role = Role::findOrFail($id);

        $role->update([
            'name' => $request->input('name'),
        ]);

        $permissions = $request->input('permission_ids');
        $role->syncPermissions($permissions);

        Session::flash('success', 'Role Updated');

        return redirect('role');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        Session::flash('success', 'Role Deleted');

        return redirect('role');
    }
}

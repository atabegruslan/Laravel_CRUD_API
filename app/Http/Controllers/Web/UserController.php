<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Session;

class UserController extends Controller
{
    private $feature = 'user';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::orderBy('updated_at', 'DESC')->paginate(env('PAG'));

        return view('user.index', ['users' => $users, 'feature' => $this->feature]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user          = User::findOrFail($id);
        $selectedRoles = $user->roles()->get();

        $data = [
            'user'            => $user, 
            'roles'           => [],
            'selectedRoles'   => $selectedRoles,
            'selectedRoleIds' => [],
            'feature'         => $this->feature,
        ];

        return view('user.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user            = User::findOrFail($id);
        $roles           = Role::all();
        $selectedRoles   = $user->roles()->get();
        $selectedRoleIds = $selectedRoles->pluck('id')->toArray();

        $data = [
            'user'            => $user, 
            'roles'           => $roles,
            'selectedRoles'   => $selectedRoles,
            'selectedRoleIds' => $selectedRoleIds,
            'feature'         => $this->feature,
        ];

        return view('user.edit', $data);
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
        $user  = User::findOrFail($id);
        $roles = $request->input('role_ids');
        $user->syncRoles($roles);

        Session::flash('success', 'User Roles Updated');

        return redirect('user');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

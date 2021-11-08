<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class GiveAdminAllPermissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ( !Role::where( 'name', env('ADMIN_NAME') )->exists() )
        {
            Role::firstOrCreate([
                'guard_name' => 'web',
                'name'       => env('ADMIN_NAME'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $adminRole = Role::where( 'name', env('ADMIN_NAME') )->first();
        $admin     = User::where( 'name', env('ADMIN_NAME') )->first();

        if ( !DB::table('model_has_roles')->where('role_id', $adminRole->id)->exists() )
        {
            DB::table('model_has_roles')->insert([
                'role_id'    => $adminRole->id, 
                'model_type' => User::class,
                'model_id'   => $admin->id,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Permission::all()->each(function($permission) use ($adminRole) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permission->id, 
                'role_id'       => $adminRole->id, 
            ]);
        });
    }
}

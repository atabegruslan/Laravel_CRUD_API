<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ( !User::where( 'email', env('ADMIN_EMAIL') )->exists() )
        {
            User::firstOrCreate([
                'email'    => env('ADMIN_EMAIL'),
                'name'     => env('ADMIN_NAME'),
                'password' => bcrypt(env('ADMIN_PASSWORD')),
            ]);
        }
    }
}

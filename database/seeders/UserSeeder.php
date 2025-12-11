<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::updateOrCreate([
            'email' => 'admin@gmail.com'
        ],[
            'name' => 'Super admin',
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'phone_number' => '1234567890',
            'password' => "12345678"
        ]);
    
        $role = Role::firstWhere([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        if ($role) {
            $role->syncPermissions(Permission::pluck('id')->toArray());
            $user->assignRole([$role->id]);
        }
        
    }
}
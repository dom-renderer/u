<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Admin',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Store Manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Store Employee',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Store Cashier',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Corporate Office Manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Divisional Operations Manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Head of Department',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Vice President',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Director',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Operations manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Store Phone',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Employee',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Ticket Agent',
                'guard_name' => 'web'
            ]
        ];

        foreach ($roles as $role) {
            \Spatie\Permission\Models\Role::firstOrCreate($role);
        }
    }
}

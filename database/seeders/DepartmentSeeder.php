<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ([
            "Operations",
            "Legal",
            "Finance",
            "Infra",
            "IT",
            "Marketing",
            "Supply Chain",
            "Compliance",
            "Accounts",
            "Project Team",
            "Online Delivery",
            "Recipe Queries",
            "Audit",
            "Maintenance",
            "Product Quality",
            "HR" 
        ] as $department) {
            \App\Models\Department::updateOrCreate([
                'name' => $department
            ]);
        }
    }
}

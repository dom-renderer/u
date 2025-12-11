<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ticketRoles = [
            'Agent',
            'Divisonal Operations Manager'
        ];

        foreach ($ticketRoles as $role) {
            \App\Models\TicketRole::updateOrCreate([
                'name' => $role,
            ]);
        }

        $dpIssues = [
            'IT' => [
                'Software not working' => [
                    'Billing Issue',
                    'Purchase',
                    'Inventory Management',
                    'Other Query'
                ],
                'CCTV Access' => [
                    'CCTV Access'
                ],
                'Hardware Issue' => [
                    'Computer',
                    'Printer',
                    'Other Query'
                ],
                'Internet Issue' => [
                    'Internet Issue'
                ]
            ],
            'Supply Chain' => [
                'Warehouse' => [
                    'Short Supply',
                    'Wrong Product Received',
                    'Damaged Product Received',
                    'Near Expiry Product Received',
                    'Quantity Variation',
                    'Track My Order',
                    'Bills Required',
                    'Promotional Material',
                    'Other Query',
                    'Purchase Return',
                    'Product Quality'
                ],
                'Dropshipment' => [
                    'Short Supply',
                    'Wrong Product Received',
                    'Damaged Product Received',
                    'Near Expiry Product Received',
                    'Quantity Variation',
                    'Track My Order',
                    'Bills Required',
                    'Other Query',
                    'Purchase Return',
                    'Product Quality'
                ],
                'Dropshipment - Bakery' => [
                    'Dropshipment - Bakery'
                ],
                'Transport Discrepancy' => [
                    'Transport Discrepancy - Received Less',
                    'Transport Discrepancy - Received More'
                ],
                'Product Quality' => [
                    'Product Quality'
                ]
            ],
            'Legal' => [
                'Agreements' => [
                    'Renewal of Agreement',
                    'Transfer of Ownership',
                    'FSSAI',
                    'Other Query',
                    'Notice',
                    'License Renewal',
                    'Apply for License',
                ],
                'Compliance' => [
                    'Pest Control',
                    'Fire Extinguisher'
                ]
            ],
            'Accounts' => [
                'Accounts' => [
                    'Statement Required',
                    'Royalty Bills Required',
                    'Online Partner Credit',
                    'Quotation v/s Actual Bills',
                    'Payment Advice Related',
                    'GST / TDS & Other Statutory Complaince Related',
                    'Other Query',
                    'Bill payments (Electricity/Gas/Maintenance/ Municipal/Water/SNEL)',
                    'Vendor Creation'
                ]
            ],
            'Marketing' => [
                'Online Promotion' => [
                    'Google Location',
                    'Social Media'
                ],
                'Designing' => [
                    'TV Menu',
                    'Offer',
                    'New Launch / Re-Launch',
                    'New Design',
                    'Translite',
                    'Hand Menu'
                ],
                'Printing' => [
                    'Hand Menu',
                    'Offer',
                    'New Launch / Re-Launch',
                    'Translite'
                ],
                'Offline Support' => [
                    'Offline Support'
                ],
                'Other Query' => [
                    'Other Query'
                ]
            ],
            'Maintenance' => [
                'Electricity / Light' => [
                    'Light Bulb Not Working',
                    'Store Non Operational'
                ],
                'Plumbing' => [
                    'Plumbing'
                ],
                'Furniture' => [
                    'Furniture'
                ],
                'Paint' => [
                    'Paint'
                ],
                'Kitchen Equipments' => [
                    'Kitchen Equipments'
                ],
                'Other Equipments' => [
                    'Other Equipments'
                ],
                'Sign Board' => [
                    'Sign Board'
                ],
                'Translite' => [
                    'Translite'
                ],
                'Ceiling' => [
                    'color'
                ],
                'Other Query' => [
                    'Other Query'
                ],
                'Civil Work' => [
                    'Civil Work'
                ],
                'Drainage' => [
                    'Drainage'
                ],
                'AC Water Leaking' => [
                    'AC Water Leaking'
                ]
            ],
            'Project Team' => [
                'Setup' => [
                    'Kitchen',
                    'other'
                ],
                'Cleaning' => [
                    'All Drawers',
                    'All Furniture Parts',
                    'Ceiling',
                    'Corners',
                    'Dine In Chairs',
                    'Dine In Tables',
                    'Floor',
                    'Glass Door',
                    'Kitchen Area',
                    'Side Glass Wall',
                    'Wall posters',
                    'other'
                ],
                'Electricals' => [
                    'AC & Ac Drainage',
                    'AC Remote Numbers',
                    'Alarm/Emergency Light',
                    'All Cameras',
                    'Electricity Load Work',
                    'Pesto Flesh / Fly Killer',
                    'Pesto Flesh / Fly Killer',
                    'other',
                ],
                'Flooring & Colour Work' => [
                    'Colour Finishing',
                    'Grouting',
                    'Tiling',
                    'other',
                ],
                'Fire Safety' => [
                    'Fire extinguisher',
                    'other'
                ],
                'Furniture' => [
                    'Furniture',
                    'other'
                ],
                'Kitchen' => [
                    'Drainage system Kitchen to main drainage',
                    'other'
                ],
                'Washroom' => [
                    'Wash Basin',
                    'Water Inlets & Outlets',
                    'WC',
                    'Exhaust Fan',
                    'other',
                ],
                'other' => [
                    'other'
                ],
                'TV Issue' => [
                    'TV Issue',
                    'other'
                ]
            ],
            'HR' => [
                'Attendance' => [
                    'Attendance',
                    'Others'
                ],
                'Salary' => [
                    'Salary',
                    'Others'
                ],
                'Biometric' => [
                    'Infogird not working',
                    'Punch In / Punch Out Issue',
                    'Fingerprint not working',
                    'Others'
                ]
            ],
            'Online Delivery' => [
                'Ownership change' => [
                    'Ownership change'
                ],
                'Images and Description related issue' => [
                    'Images and Description related issue',
                    'Images and Description Issue'
                ],
                'Others' => [
                    'Other Query'
                ],
                'New Outlet Onboarding' => [
                    'New Outlet Onboarding'
                ],
                'Ownership Change' => [
                    'Ownership change'
                ],
                'Discounts related issue' => [
                    'Discounts related issue'
                ],
                'CPC ADS' => [
                    'CPC ADS'
                ]
            ],
            'Audit' => [
                'Short Sales' => [
                    'Short Sales'
                ],
                'NON TPPL' => [
                    'NON TPPL'
                ],
                'Others' => [
                    'Others'
                ]
            ],
            'Recipe Queries' => [
                'Product Issue' => [
                    'Premix Batch Problem'
                ],
                'Recipe Training' => [
                    'Product Training',
                    'Recipe Chart Issue'
                ]
            ],
            'Product Quality' => [
                'Product Quality' => [
                    'Product Quality',
                    'Other'
                ]
            ]
        ];

        foreach ($dpIssues as $department => $particulars) {
            $departmentEl = \App\Models\Department::where('name', $department)->first();
            if ($departmentEl) {
                foreach ($particulars as $particular => $issues) {
                    $particularEl = \App\Models\Particular::updateOrCreate([
                        'name' => $particular,
                        'department_id' => $departmentEl->id
                    ],
            [
                        'status' => 1
                    ]);

                    foreach ($issues as $issue) {
                        \App\Models\Issue::updateOrCreate([
                            'name' => $issue,
                            'department_id' => $departmentEl->id,
                            'particular_id' => $particularEl->id
                        ],
                [
                            'status' => 1
                        ]);
                    }
                }
            }
        }

    }
}

<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;

class DocumentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $documents = [
            'FSSAI',
            'Fire NOC',
            'Shop Establishment',
            'Rent Agreement',
            'Franchise Agreement',
        ];

        foreach ( $documents as $document ) {
            Document::firstOrCreate(
                [ 'name' => $document ],
                [ 'name' => $document, 'created_at' => \Carbon\Carbon::now(), 'updated_at' => \Carbon\Carbon::now() ]
            );
        }
    }
}

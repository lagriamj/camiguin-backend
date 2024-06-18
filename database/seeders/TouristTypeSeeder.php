<?php

namespace Database\Seeders;

use App\Models\TouristType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TouristTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            [
                "name" => 'Local Adult',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                "name" => 'Tourist Adult',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                "name" => 'Student/PWD/5 years old below',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        TouristType::truncate();
        TouristType::insert($fields);
    }
}

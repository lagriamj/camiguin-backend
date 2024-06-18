<?php

namespace Database\Seeders;

use App\Models\DestinationCategory;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InitialDestinationCategories extends Seeder
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
                "name" => 'Category one',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        DestinationCategory::truncate();
        DestinationCategory::insert($fields);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Kiosk;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class KioskSeeder extends Seeder
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
                "destination_id" => 13,
                "qr_code" => 'CCQRRDT2LM',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                "destination_id" => 14,
                "qr_code" => 'CCQRRDT2LL',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                "destination_id" => 12,
                "qr_code" => 'CCQRRDT2LV',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ],
            [
                "destination_id" => 11,
                "qr_code" => 'CCQRRDT2LB',
                "created_at" => Carbon::now()->format('Y-m-d H:i:s'),
                "updated_at" => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        Kiosk::truncate();
        Kiosk::insert($fields);
    }
}
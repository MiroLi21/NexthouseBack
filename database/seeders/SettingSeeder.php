<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Setting::create([
            'key' => 'DateInteraction',
            'value1' => 10,
            // 'value2' => '',
            // 'value3' => '',
            // 'value4' => '',
            // 'value5' => '',
            'description' => 'الوقت المتاح للتواصل مع العميل',
            'is_active' => true,
        ]);
    }
}

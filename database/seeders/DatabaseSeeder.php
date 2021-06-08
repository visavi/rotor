<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BlacklistSeeder::class,
            CounterSeeder::class,
            NoticeSeeder::class,
            RuleSeeder::class,
            SettingSeeder::class,
            StatusSeeder::class,
            StickerSeeder::class,
        ]);
    }
}

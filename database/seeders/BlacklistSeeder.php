<?php

namespace Database\Seeders;

use App\Models\BlackList;
use Illuminate\Database\Seeder;

class BlacklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'type'       => 'domain',
            'value'      => 'asdasd.ru',
            'created_at' => SITETIME,
            'user_id'    => 1,
        ];

        BlackList::query()->truncate();
        BlackList::query()->insert($data);
    }
}

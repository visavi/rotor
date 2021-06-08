<?php

namespace Database\Seeders;

use App\Models\BlackList;
use Illuminate\Database\Seeder;

class BlacklistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
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

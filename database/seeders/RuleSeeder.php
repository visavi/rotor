<?php

namespace Database\Seeders;

use App\Models\Rule;
use Illuminate\Database\Seeder;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'id'         => 1,
            'text'       => __('seeds.rules'),
            'created_at' => SITETIME,
        ];

        Rule::query()->truncate();
        Rule::query()->insert($data);
    }
}

<?php

use Phinx\Seed\AbstractSeed;

class RulesSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        $this->execute('TRUNCATE rules');

        $data = [
            'id'   => 1,
            'text' => __('seeds.rules'),
            'created_at' => SITETIME,
        ];

        $table = $this->table('rules');
        $table->insert($data)->save();
    }
}

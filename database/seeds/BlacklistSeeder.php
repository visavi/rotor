<?php

use Phinx\Seed\AbstractSeed;

class BlacklistSeeder extends AbstractSeed
{
    /**
     * Run Method.
     */
    public function run(): void
    {
        $this->execute('TRUNCATE blacklist');

        $table = $this->table('blacklist');
        $table->insert([
            'type'       => 'domain',
            'value'      => 'asdasd.ru',
            'created_at' => SITETIME,
            'user_id'    => 1,
            ])
            ->save();
    }
}

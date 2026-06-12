<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE settings DROP PRIMARY KEY, MODIFY name VARCHAR(50) NOT NULL, ADD PRIMARY KEY (name)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE settings DROP PRIMARY KEY, MODIFY name VARCHAR(25) NOT NULL, ADD PRIMARY KEY (name)');
    }
};

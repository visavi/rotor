<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('pollings', 'polls');
    }

    public function down(): void
    {
        Schema::rename('polls', 'pollings');
    }
};

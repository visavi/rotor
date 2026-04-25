<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('name', 'previewsize')->delete();
    }

    public function down(): void
    {
        DB::table('settings')->insert(['name' => 'previewsize', 'value' => 500]);
    }
};

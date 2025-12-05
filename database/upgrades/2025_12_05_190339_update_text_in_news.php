<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('news')
            ->where('text', 'LIKE', '%[cut]%')
            ->update([
                'text' => DB::raw("REPLACE(text, '[cut]', '')"),
            ]);
    }

    public function down(): void
    {
        //
    }
};

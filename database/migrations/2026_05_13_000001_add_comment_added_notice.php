<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (! DB::table('notices')->where('type', 'comment_added')->exists()) {
            DB::table('notices')->insert([
                'type'       => 'comment_added',
                'name'       => __('seeds.notices.comment_added_name'),
                'text'       => __('seeds.notices.comment_added_text'),
                'user_id'    => 1,
                'created_at' => SITETIME,
                'updated_at' => SITETIME,
                'protect'    => 1,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('notices')->where('type', 'comment_added')->delete();
    }
};

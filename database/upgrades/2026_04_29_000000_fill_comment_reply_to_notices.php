<?php

declare(strict_types=1);

use App\Models\Notice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        if (Notice::query()->where('type', 'comment_reply')->exists()) {
            return;
        }

        Notice::query()->create([
            'type'       => 'comment_reply',
            'name'       => __('seeds.notices.comment_reply_name'),
            'text'       => __('seeds.notices.comment_reply_text'),
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ]);
    }

    public function down(): void
    {
        Notice::query()->where('type', 'comment_reply')->delete();
    }
};

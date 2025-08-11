<?php

use App\Models\Notice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Notice::query()->create([
            'type'       => 'offer_reply',
            'name'       => __('seeds.notices.offer_reply_name'),
            'text'       => __('seeds.notices.offer_reply_text'),
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ]);
    }

    public function down(): void
    {
        Notice::query()->where('type', 'offer_reply')->delete();
    }
};

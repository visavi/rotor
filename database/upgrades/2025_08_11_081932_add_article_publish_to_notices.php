<?php

use App\Models\Notice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Notice::query()->create([
            'type'       => 'article_publish',
            'name'       => __('seeds.notices.article_publish_name'),
            'text'       => __('seeds.notices.article_publish_text'),
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ]);

        Notice::query()->create([
            'type'       => 'article_unpublish',
            'name'       => __('seeds.notices.article_unpublish_name'),
            'text'       => __('seeds.notices.article_unpublish_text'),
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ]);
    }

    public function down(): void
    {
        Notice::query()->where('type', 'article_publish')->delete();
        Notice::query()->where('type', 'article_unpublish')->delete();
    }
};

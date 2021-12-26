<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

final class AddFeedToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Setting::query()->where('name', 'homepage_view')->updateOrCreate([], [
            'name'  => 'homepage_view',
            'value' => 'feed',
        ]);

        Setting::query()->where('name', 'feed_topics_show')->updateOrCreate([], [
            'name'  => 'feed_topics_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_news_show')->updateOrCreate([], [
            'name'  => 'feed_news_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_photos_show')->updateOrCreate([], [
            'name'  => 'feed_photos_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_articles_show')->updateOrCreate([], [
            'name'  => 'feed_articles_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_downs_show')->updateOrCreate([], [
            'name'  => 'feed_downs_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_items_show')->updateOrCreate([], [
            'name'  => 'feed_items_show',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'feed_per_page')->updateOrCreate([], [
            'name'  => 'feed_per_page',
            'value' => 20,
        ]);

        Setting::query()->where('name', 'feed_last_record')->updateOrCreate([], [
            'name'  => 'feed_last_record',
            'value' => 20,
        ]);

        Setting::query()->where('name', 'feed_total')->updateOrCreate([], [
            'name'  => 'feed_total',
            'value' => 100,
        ]);

        Setting::query()->where('name', 'feed_topics_rating')->updateOrCreate([], [
            'name'  => 'feed_topics_rating',
            'value' => -10,
        ]);

        Setting::query()->where('name', 'feed_news_rating')->updateOrCreate([], [
            'name'  => 'feed_news_rating',
            'value' => -10,
        ]);

        Setting::query()->where('name', 'feed_photos_rating')->updateOrCreate([], [
            'name'  => 'feed_photos_rating',
            'value' => -10,
        ]);

        Setting::query()->where('name', 'feed_articles_rating')->updateOrCreate([], [
            'name'  => 'feed_articles_rating',
            'value' => -10,
        ]);

        Setting::query()->where('name', 'feed_downs_rating')->updateOrCreate([], [
            'name'  => 'feed_downs_rating',
            'value' => -10,
        ]);


        clearCache('settings');
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Setting::query()->where('name', 'homepage_view')->delete();
        Setting::query()->where('name', 'feed_topics_show')->delete();
        Setting::query()->where('name', 'feed_news_show')->delete();
        Setting::query()->where('name', 'feed_photos_show')->delete();
        Setting::query()->where('name', 'feed_articles_show')->delete();
        Setting::query()->where('name', 'feed_downs_show')->delete();
        Setting::query()->where('name', 'feed_items_show')->delete();
        Setting::query()->where('name', 'feed_per_page')->delete();
        Setting::query()->where('name', 'feed_last_record')->delete();
        Setting::query()->where('name', 'feed_total')->delete();
        Setting::query()->where('name', 'feed_topics_rating')->delete();
        Setting::query()->where('name', 'feed_news_rating')->delete();
        Setting::query()->where('name', 'feed_photos_rating')->delete();
        Setting::query()->where('name', 'feed_articles_rating')->delete();
        Setting::query()->where('name', 'feed_downs_rating')->delete();
    }
}

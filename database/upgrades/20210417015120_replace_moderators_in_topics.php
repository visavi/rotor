<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\Topic;
use Illuminate\Database\Schema\Blueprint;

final class ReplaceModeratorsInTopics extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $topics = Topic::query()->where('moderators', '<>', '')->whereNotNull('moderators')->get();

        foreach ($topics as $topic) {
            $moderatorLogins = [];
            $moderators = explode(',', $topic->moderators);

            foreach ($moderators as $moderator) {
                if ($moder = getUserById((int) $moderator)) {
                    $moderatorLogins[] = $moder->login;
                }
            }

            $topic->update(['moderators' => implode(',', $moderatorLogins)]);
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $topics = Topic::query()->where('moderators', '<>', '')->whereNotNull('moderators')->get();

        foreach ($topics as $topic) {
            $moderatorLogins = [];
            $moderators = explode(',', $topic->moderators);

            foreach ($moderators as $moderator) {
                if ($moder = getUserByLogin((string) $moderator)) {
                    $moderatorLogins[] = $moder->id;
                }
            }

            $topic->update(['moderators' => implode(',', $moderatorLogins)]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'comments',
            'readers',
            'pollings',
            'files',
            'search',
            'spam',
        ];

        $relateTypes = [
            'down'    => 'downs',
            'article' => 'articles',
            'photo'   => 'photos',
            'offer'   => 'offers',
            'topic'   => 'topics',
            'post'    => 'posts',
            'message' => 'messages',
            'wall'    => 'walls',
            'comment' => 'comments',
            'vote'    => 'votes',
            'item'    => 'items',
            'user'    => 'users',
        ];

        DB::connection()->unsetEventDispatcher();

        foreach ($tables as $table) {
            foreach ($relateTypes as $oldType => $newType) {
                DB::table($table)
                    ->where('relate_type', $oldType)
                    ->update(['relate_type' => $newType]);
            }
        }
    }

    public function down(): void
    {
        //
    }
};

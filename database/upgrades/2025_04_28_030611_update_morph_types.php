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
            'spam',
        ];

        $relateTypes = [
            'downs'    => 'down',
            'articles' => 'article',
            'photos'   => 'photo',
            'offers'   => 'offer',
            'topics'   => 'topic',
            'posts'    => 'post',
            'messages' => 'message',
            'walls'    => 'wall',
            'comments' => 'comment',
            'votes'    => 'vote',
            'items'    => 'item',
        ];

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

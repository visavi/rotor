<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stickers')
            ->where('code', 'like', ':%')
            ->get()
            ->each(function ($sticker) {
                DB::table('stickers')
                    ->where('id', $sticker->id)
                    ->update(['code' => ltrim($sticker->code, ':')]);
            });

        Cache::deleteMultiple(['stickers', 'stickers_map']);
    }

    public function down(): void
    {
        DB::table('stickers')
            ->get()
            ->each(function ($sticker) {
                DB::table('stickers')
                    ->where('id', $sticker->id)
                    ->update(['code' => ':' . $sticker->code]);
            });

        Cache::deleteMultiple(['stickers', 'stickers_map']);
    }
};

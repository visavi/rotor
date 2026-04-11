<?php

declare(strict_types=1);

use App\Classes\BBMigrator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guestbook', static function (Blueprint $table) {
            $table->text('text2')->nullable()->after('text');
        });

        DB::table('guestbook')
            ->orderBy('id')
            ->chunk(200, static function ($records) {
                foreach ($records as $record) {
                    DB::table('guestbook')
                        ->where('id', $record->id)
                        ->update([
                            'text2' => BBMigrator::convert($record->text),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('guestbook', static function (Blueprint $table) {
            $table->dropColumn('text2');
        });
    }
};

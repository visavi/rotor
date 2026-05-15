<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->renameColumn('notify', 'notify_mention');
            $table->boolean('notify_reply')->default(true)->after('notify_mention');
            $table->boolean('notify_comment')->default(true)->after('notify_reply');
        });
    }

    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['notify_reply', 'notify_comment']);
            $table->renameColumn('notify_mention', 'notify');
        });
    }
};

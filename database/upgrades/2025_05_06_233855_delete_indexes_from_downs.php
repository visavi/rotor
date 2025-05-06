<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('downs', function (Blueprint $table) {
            $table->dropFullText('downs_title_text_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('downs', function (Blueprint $table) {
            $table->fulltext(['title', 'text']);
        });
    }
};

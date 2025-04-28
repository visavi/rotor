<?php

use App\Models\Banhist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('banhist')) {
            Schema::create('banhist', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('send_user_id');
                $table->enum('type', [Banhist::BAN, Banhist::UNBAN, Banhist::CHANGE]);
                $table->text('reason');
                $table->integer('term')->default(0);
                $table->integer('created_at');
                $table->boolean('explain')->default(false);

                $table->index('user_id');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banhist');
    }
};

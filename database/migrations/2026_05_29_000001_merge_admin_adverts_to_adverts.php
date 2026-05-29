<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->string('type', 10)->default('user')->after('bold');
        });

        if (Schema::hasTable('admin_adverts')) {
            Schema::dropIfExists('admin_adverts');
        }
    }

    public function down(): void
    {
        Schema::create('admin_adverts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('site', 100);
            $table->string('name', 50);
            $table->string('color', 10)->nullable();
            $table->boolean('bold')->default(false);
            $table->integer('user_id');
            $table->integer('created_at');
            $table->integer('deleted_at')->nullable();
        });

        DB::table('adverts')->where('type', 'admin')->delete();

        Schema::table('adverts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

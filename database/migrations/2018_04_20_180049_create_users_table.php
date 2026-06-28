<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('login', 20);
                $table->string('password', 128);
                $table->string('email', 100);
                $table->string('level', 20)->default(User::PENDED);
                $table->string('name', 20)->nullable();
                $table->string('country', 30)->nullable();
                $table->string('city', 50)->nullable();
                $table->string('language', 2)->nullable();
                $table->text('info')->nullable();
                $table->string('site', 50)->nullable();
                $table->string('phone', 15)->nullable();
                $table->enum('gender', [User::MALE, User::FEMALE]);
                $table->string('birthday', 10)->nullable();
                $table->integer('newprivat')->default(0);
                $table->string('themes', 20)->nullable();
                $table->string('timezone', 3)->default('0');
                $table->integer('point')->default(0);
                $table->integer('money')->default(0);
                $table->dateTime('timeban')->nullable();
                $table->string('status', 50)->nullable();
                $table->string('color', 10)->nullable();
                $table->string('avatar', 100)->nullable();
                $table->string('picture', 100)->nullable();
                $table->integer('rating')->default(0);
                $table->integer('posrating')->default(0);
                $table->integer('negrating')->default(0);
                $table->boolean('sendprivatmail')->default(false);
                $table->dateTime('timebonus')->nullable();
                $table->integer('newchat')->nullable();
                $table->boolean('notify_mention')->default(true);
                $table->boolean('notify_reply')->default(true);
                $table->boolean('notify_comment')->default(true);
                $table->string('apikey', 32)->nullable();
                $table->string('subscribe', 32)->nullable();
                $table->string('confirm_token', 100)->nullable();
                $table->rememberToken();
                $table->dateTime('updated_at')->nullable();
                $table->dateTime('created_at')->nullable();

                $table->unique('login');
                $table->unique('email');
                $table->index('level');
                $table->index('point');
                $table->index('money');
                $table->index('rating');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

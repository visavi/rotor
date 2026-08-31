<?php

declare(strict_types=1);

use App\Services\UserService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // regkeys ничего не говорил о смысле и описывал лишь половину шкалы.
        // Одна настройка вместо флага: скрыта / необязательна / обязательна / с подтверждением
        $confirm = (bool) DB::table('settings')->where('name', 'regkeys')->value('value');

        DB::table('settings')->insertOrIgnore([
            [
                'name'  => 'email_mode',
                'value' => $confirm ? UserService::EMAIL_CONFIRM : UserService::EMAIL_REQUIRED,
            ],
        ]);

        DB::table('settings')->where('name', 'regkeys')->delete();
    }

    public function down(): void
    {
        $confirm = DB::table('settings')->where('name', 'email_mode')->value('value');

        DB::table('settings')->insertOrIgnore([
            ['name' => 'regkeys', 'value' => $confirm === UserService::EMAIL_CONFIRM ? 1 : 0],
        ]);

        DB::table('settings')->where('name', 'email_mode')->delete();
    }
};

<?php

use App\Models\Notice;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Notice::query()->create([
            'type' => 'offer_reply',
            'name' => 'Ответ на проблему / предложение',
            'text' => <<<'INFO'
Уведомление об ответе на вашу проблему / предложение
На вашу проблему или предложение [b][url=%url%]%title%[/url][/b] ответили
Текст ответа: %text%
Статус записи: %status%
INFO,
            'user_id'    => 1,
            'created_at' => SITETIME,
            'updated_at' => SITETIME,
            'protect'    => 1,
        ]);
    }

    public function down(): void
    {
        Notice::query()->where('type', 'offer_reply')->delete();
    }
};

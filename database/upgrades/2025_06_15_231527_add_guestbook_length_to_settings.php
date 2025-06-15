<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'guestbook_text_min')->updateOrCreate([], [
            'name'  => 'guestbook_text_min',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'guestbook_text_max')->updateOrCreate([], [
            'name'  => 'guestbook_text_max',
            'value' => 1000,
        ]);

        // Удаляем предыдущую настройку
        Setting::query()->where('name', 'guesttextlength')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'guestbook_text_min')->delete();
        Setting::query()->where('name', 'guestbook_text_max')->delete();

        Setting::query()->where('name', 'guesttextlength')->updateOrCreate([], [
            'name'  => 'guesttextlength',
            'value' => 1000,
        ]);
    }
};

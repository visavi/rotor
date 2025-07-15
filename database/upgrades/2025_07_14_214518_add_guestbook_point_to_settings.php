<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'guestbook_point')->updateOrCreate([], [
            'name'  => 'guestbook_point',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'guestbook_money')->updateOrCreate([], [
            'name'  => 'guestbook_money',
            'value' => 50,
        ]);

        Setting::query()->where('name', 'bookscores')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'guestbook_point')->delete();
        Setting::query()->where('name', 'guestbook_money')->delete();

        Setting::query()->where('name', 'bookscores')->updateOrCreate([], [
            'name'  => 'bookscores',
            'value' => 0,
        ]);
    }
};

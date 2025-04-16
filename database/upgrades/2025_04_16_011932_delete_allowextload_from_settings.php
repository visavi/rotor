<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'allowextload')->delete();
        Setting::query()->where('name', 'fileupload')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'allowextload')->updateOrCreate([], [
            'name'  => 'allowextload',
            'value' => 'zip,rar,txt,jpg,jpeg,gif,png,mp3,mp4,pdf',
        ]);

        Setting::query()->where('name', 'fileupload')->updateOrCreate([], [
            'name'  => 'fileupload',
            'value' => 10485760,
        ]);
    }
};

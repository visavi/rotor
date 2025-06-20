<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'captcha_angle')->delete();
        Setting::query()->where('name', 'captcha_offset')->delete();
        Setting::query()->where('name', 'captcha_distortion')->delete();
        Setting::query()->where('name', 'captcha_interpolation')->delete();
        Setting::query()->where('name', 'captcha_spaces')->delete();

        clearCache('settings');
    }

    public function down(): void
    {
        Setting::query()->where('name', 'captcha_angle')->updateOrCreate([], [
            'name'  => 'captcha_angle',
            'value' => 20,
        ]);

        Setting::query()->where('name', 'captcha_offset')->updateOrCreate([], [
            'name'  => 'captcha_offset',
            'value' => 5,
        ]);

        Setting::query()->where('name', 'captcha_distortion')->updateOrCreate([], [
            'name'  => 'captcha_distortion',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'captcha_interpolation')->updateOrCreate([], [
            'name'  => 'captcha_interpolation',
            'value' => 1,
        ]);

        Setting::query()->where('name', 'captcha_spaces')->updateOrCreate([], [
            'name'  => 'captcha_spaces',
            'value' => 0,
        ]);
    }
};

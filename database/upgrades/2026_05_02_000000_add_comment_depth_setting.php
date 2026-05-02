<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Setting::query()->where('name', 'comment_depth')->updateOrCreate([], [
            'name'  => 'comment_depth',
            'value' => 3,
        ]);
    }

    public function down(): void
    {
        Setting::query()->where('name', 'comment_depth')->delete();
    }
};

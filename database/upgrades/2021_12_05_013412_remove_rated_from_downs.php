<?php

declare(strict_types=1);

use App\Models\Down;
use App\Models\Polling;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class RemoveRatedFromDowns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $downs = Down::query()->get();

        foreach ($downs as $down) {
            $newRating = 0;
            $avgRating = $down->rated ? $down->rating / $down->rated : 0;

            if ($avgRating >= 4) {
                $newRating = $down->rated; // 100 проц лайков
            }

            if ($avgRating >= 3.5 && $avgRating < 4) {
                $newRating = $down->rated - round($down->rated / 100 * 25); // 75 проц лайков
            }

            if ($avgRating >= 3 && $avgRating < 3.5) {
                $newRating = round($down->rated / 2); // 50 проц лайков
            }

            if ($avgRating >= 2.5 && $avgRating < 3) {
                $newRating = round($down->rated - round($down->rated / 100 * 75)); // 25 проц лайков
            }

            if ($avgRating >= 2 && $avgRating < 2.5) {
                $newRating = -round($down->rated / 2); // 50 проц дизлайков
            }

            if ($avgRating >= 1.5 && $avgRating < 2) {
                $newRating = -round($down->rated - round($down->rated / 100 * 25)); // 75 проц дизлайков
            }

            if ($avgRating < 1.5) {
                $newRating = -$down->rated; // 100 проц дизлайков
            }

            if ($down->rated === 0) {
                $newRating = 0;
            }

            Down::query()
                ->where('id', $down->id)
                ->update(['rating' => $newRating]);
        }

        Polling::query()
            ->where('relate_type', Down::$morphName)
            ->whereIn('vote', [3, 4, 5])
            ->update(['vote' => '+']);

        Polling::query()
            ->where('relate_type', Down::$morphName)
            ->whereIn('vote', [1, 2])
            ->update(['vote' => '-']);

        Schema::table('downs', function (Blueprint $table) {
            $table->dropColumn('rated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('errors', function (Blueprint $table) {
            $table->integer('rated')->default(0)->after('rating');
        });
    }
}

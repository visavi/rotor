<?php

use App\Models\News;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $newsWithImages = DB::table('news')
            ->whereNotNull('image')
            ->where('image', '<>', '')
            ->get();

        foreach ($newsWithImages as $news) {
            $imagePath = public_path($news->image);
            $size = file_exists($imagePath) ? filesize($imagePath) : 0;

            DB::table('files')->insert([
                'hash'        => $news->image,
                'name'        => 'news' . $news->id . '.' . getExtension($news->image),
                'size'        => $size,
                'user_id'     => $news->user_id,
                'relate_type' => News::$morphName,
                'relate_id'   => $news->id,
                'created_at'  => $news->created_at,
            ]);
        }

        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->string('image', 100)->nullable()->after('user_id');
        });

        $files = DB::table('files')
            ->where('relate_type', News::$morphName)
            ->get();

        foreach ($files as $file) {
            DB::table('news')
                ->where('id', $file->relate_id)
                ->update(['image' => $file->hash]);
        }

        DB::table('files')
            ->where('relate_type', News::$morphName)
            ->delete();
    }
};

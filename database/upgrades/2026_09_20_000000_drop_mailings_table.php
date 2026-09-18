<?php

use App\Services\MailService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Переносит недоставленные письма в очередь и убирает таблицу
     *
     * Строки со sent = 0 — это письма, до которых рассылка не дошла.
     * Терять их при обновлении незачем, а отправлять напрямую нельзя:
     * обновление не должно упираться в почтовый сервер
     */
    public function up(): void
    {
        if (! Schema::hasTable('mailings')) {
            return;
        }

        $mail = app(MailService::class);

        DB::table('mailings')
            ->where('sent', 0)
            ->orderBy('id')
            ->chunkById(100, static function ($rows) use ($mail) {
                foreach ($rows as $row) {
                    $email = DB::table('users')->where('id', $row->user_id)->value('email');

                    if (! $email) {
                        continue;
                    }

                    $mail->queue('mailer.default', [
                        'to'      => $email,
                        'subject' => $row->subject,
                        'text'    => $row->text,
                    ]);
                }
            });

        Schema::drop('mailings');
    }

    public function down(): void
    {
        // Таблица восстановлению не подлежит: её содержимое уехало в очередь
    }
};

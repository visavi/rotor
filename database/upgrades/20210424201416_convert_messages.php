<?php

declare(strict_types=1);

use App\Migrations\Migration;
use App\Models\Dialogue;
use App\Models\Message;
use App\Models\Message2;

final class ConvertMessages extends Migration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $messages = Message::query()->orderBy('created_at')->get();

        /** @var Message $message */
        foreach ($messages as $message) {
            $hash = $message->user_id . '-' . $message->author_id . '-' . $message->created_at;
            $hash2 = $message->author_id . '-' . $message->user_id . '-' . $message->created_at;

            $message2 = Message2::query()
                ->where('hash', $hash)
                ->orWhere('hash', $hash2)
                ->first();

            if (! $message2) {
                if ($message->type === Message::OUT) {
                    $userOut = $message->user_id;
                    $userIn = $message->author_id;
                } else {
                    $userOut = $message->author_id;
                    $userIn = $message->user_id;
                }

                $message2 = Message2::query()->create([
                    'user_id'    => $userIn,
                    'author_id'  => $userOut,
                    'text'       => $message->text,
                    'created_at' => $message->created_at,
                    'hash'       => $hash,
                ]);
            }

            $hash = $message->user_id . '-' . $message->author_id . '-' . $message2->id;

            $dial = Dialogue::query()->where('hash', $hash)->first();
            if ($dial) {
                continue;
            }

            $dialogue = new Dialogue();
            $dialogue->message_id = $message2->id;
            $dialogue->user_id    = $message->user_id;
            $dialogue->author_id  = $message->author_id;
            $dialogue->type       = $message->type;
            $dialogue->reading    = $message->reading;
            $dialogue->created_at = $message->created_at;
            $dialogue->hash       = $hash;
            $dialogue->save();
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}

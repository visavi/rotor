<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Message;
use App\Models\Spam;
use App\Support\Registry;
use Illuminate\Database\Eloquent\Model;

/**
 * Жалобы на записи
 *
 * Типы записей приходят от модулей: ядро знает только комментарии и личные сообщения
 */
class ComplaintService
{
    /**
     * Типы, на которые можно пожаловаться
     *
     * @return array<int, string>
     */
    public static function types(): array
    {
        return array_values(array_unique(array_merge(
            [Comment::$morphName, Message::$morphName],
            array_keys(Registry::$complaintTypes),
        )));
    }

    /**
     * Запись жалобы, вторая жалоба на ту же запись не принимается
     *
     * @return array{success: bool, message: string}
     */
    public function create(string $type, int $id, mixed $page = null): array
    {
        [$model, $path] = $this->resolve($type, $id, $page);

        if (! $model) {
            return ['success' => false, 'message' => __('main.message_not_found')];
        }

        if (Spam::query()->where(['relate_type' => $type, 'relate_id' => $id])->exists()) {
            return ['success' => false, 'message' => __('ajax.complaint_already_sent')];
        }

        Spam::query()->create([
            'relate_type' => $type,
            'relate_id'   => $model->getKey(),
            'user_id'     => getUser('id'),
            'path'        => $path,
        ]);

        return ['success' => true, 'message' => __('ajax.complaint_success_sent')];
    }

    /**
     * Запись и ссылка на неё
     *
     * @return array{0: ?Model, 1: ?string}
     */
    private function resolve(string $type, int $id, mixed $page): array
    {
        if ($type === Message::$morphName) {
            return [Message::query()->find($id), null];
        }

        if ($type === Comment::$morphName) {
            $model = Comment::query()->find($id);

            return [$model, $model?->getViewUrl(false)];
        }

        if (isset(Registry::$complaintTypes[$type])) {
            $result = (Registry::$complaintTypes[$type])($id, $page);

            return [$result['model'] ?? null, $result['path'] ?? null];
        }

        return [null, null];
    }
}

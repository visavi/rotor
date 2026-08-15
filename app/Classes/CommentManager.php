<?php

declare(strict_types=1);

namespace App\Classes;

use App\Models\Comment;
use App\Models\File;
use App\Models\User;
use App\Traits\CommentableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Создание, правка и удаление комментариев — общее для сайта и API
 */
class CommentManager
{
    /**
     * Время, в течение которого автор может править свой комментарий
     */
    public const EDIT_MINUTES = 10;

    /**
     * Разделы, которые принимают комментарии
     */
    public static function types(): array
    {
        $types = [];

        foreach (Relation::morphMap() as $morphName => $class) {
            if (in_array(CommentableTrait::class, class_uses_recursive($class), true)) {
                $types[] = $morphName;
            }
        }

        return $types;
    }

    /**
     * Создает комментарий: считает уровень вложенности, привязывает файлы,
     * начисляет баллы автору и рассылает уведомления
     */
    public function create(Model $model, User $user, string $text, ?int $parentId = null, ?string $baseUrl = null): Comment
    {
        [$parentId, $depth, $parentComment] = $this->resolveParent($model, $parentId);

        $comment = $model->morphMany(Comment::class, 'relate')->create([
            'text'      => $text,
            'user_id'   => $user->id,
            'parent_id' => $parentId,
            'depth'     => $depth,
            'ip'        => getIp(),
            'brow'      => getBrowser(),
        ]);

        // Файлы, загруженные до отправки, ждут с relate_id = 0
        File::query()
            ->where('relate_type', Comment::$morphName)
            ->where('relate_id', 0)
            ->where('user_id', $user->id)
            ->update(['relate_id' => $comment->id]);

        $user->increment('point', setting('comment_point'));
        $user->increment('money', setting('comment_money'));

        $model->increment('count_comments');

        $this->notify($model, $comment, $text, $parentComment, $baseUrl);

        return $comment;
    }

    /**
     * Меняет текст комментария
     */
    public function update(Comment $comment, string $text): Comment
    {
        $comment->update(['text' => $text]);

        return $comment;
    }

    /**
     * Удаляет комментарий, ветку с ответами оставляет заглушкой
     *
     * @return bool Комментарий удален мягко
     */
    public function delete(Comment $comment): bool
    {
        if ($comment->children()->exists()) {
            $comment->softDelete();

            return true;
        }

        $relateType = $comment->relate_type;
        $relateId = $comment->relate_id;

        $comment->delete();

        $class = Relation::getMorphedModel($relateType);
        $model = $class ? $class::query()->find($relateId) : null;
        $model?->decrement('count_comments');

        return false;
    }

    /**
     * Проверяет, может ли автор еще править комментарий
     */
    public function editable(Comment $comment): bool
    {
        return $comment->created_at->gte(now()->subMinutes(self::EDIT_MINUTES));
    }

    /**
     * Определяет родителя и уровень вложенности ответа
     *
     * @return array{0: ?int, 1: int, 2: ?Comment}
     */
    private function resolveParent(Model $model, ?int $parentId): array
    {
        $parentComment = $parentId ? Comment::query()->find($parentId) : null;

        if (! $parentComment || $parentComment->relate_id !== $model->getKey()) {
            return [null, 0, null];
        }

        // Глубже настройки ветка не растет, ответ становится соседом родителя
        if ($parentComment->depth >= setting('comment_depth')) {
            return [$parentComment->parent_id, $parentComment->depth, $parentComment];
        }

        return [$parentComment->id, $parentComment->depth + 1, $parentComment];
    }

    /**
     * Уведомляет автора записи, автора комментария-родителя и упомянутых
     */
    private function notify(Model $model, Comment $comment, string $text, ?Comment $parentComment, ?string $baseUrl): void
    {
        $baseUrl ??= method_exists($model, 'getViewUrl') ? $model->getViewUrl(false) : null;

        if (! $baseUrl) {
            return;
        }

        $url = $baseUrl . '#comment_' . $comment->id;

        $title = (string) $model->getAttribute('title');
        $skip = [];

        $owner = $model->getRelationValue('user');
        $owner = $owner instanceof User && $owner->exists ? $owner : null;

        if ($owner && $owner->notify_comment && $owner->id !== getUser('id') && $parentComment === null) {
            $login = getUser('login');
            $owner->sendMessage(null, textNotice('comment_added', compact('login', 'url', 'title') + ['text' => $text]));
            $skip[] = $owner->login;
        }

        $replyUser = $parentComment?->user?->exists ? $parentComment->user : null;

        if ($replyUser && ! in_array($replyUser->login, $skip, true) && $replyUser->notify_reply && $replyUser->id !== getUser('id')) {
            $login = getUser('login');
            $replyUser->sendMessage(null, textNotice('comment_reply', compact('login', 'url', 'title') + ['text' => $text]));
            $skip[] = $replyUser->login;
        }

        sendNotify($text, $url, $title, $skip);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Poll;
use App\Models\User;
use App\Support\Registry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class RatingService
{
    /**
     * Типы записей, поддерживающие голосование
     */
    public static function types(): array
    {
        return array_merge([Comment::$morphName], Registry::$ratingTypes);
    }

    /**
     * Голосует за запись
     *
     * Возвращает результат голосования, а не готовый ответ: форму ответа
     * выбирает вызывающий — сайту нужен перерисованный блок, API — числа.
     * У отказа есть HTTP-статус: клиенту API по нему видно, что случилось
     *
     * @return array{success: bool, status?: int, message?: string, cancel?: bool, post?: Model, vote?: string|null}
     */
    public function vote(User $user, ?string $type, int $id, ?string $vote): array
    {
        if (! in_array($type, self::types(), true) || ! in_array($vote, ['+', '-'], true)) {
            return ['success' => false, 'status' => 422, 'message' => __('main.vote_invalid')];
        }

        /** @var class-string<Model> $model */
        $model = Relation::getMorphedModel($type);
        $post = $model::query()->find($id);

        if (! $post) {
            return ['success' => false, 'status' => 404, 'message' => __('main.record_not_found')];
        }

        if ((int) $post->getAttribute('user_id') === $user->id) {
            return ['success' => false, 'status' => 403, 'message' => __('main.vote_own')];
        }

        $poll = $this->pollRelation($post, $user)->firstOrNew();
        $isCancel = false;

        if ($poll->exists) {
            if ($poll->vote === $vote) {
                return ['success' => false, 'status' => 422, 'message' => __('main.vote_repeat')];
            }

            $isCancel = true;
            $poll->delete();
        }

        if (! $isCancel) {
            $this->pollRelation($post, $user)->create([
                'user_id' => $user->id,
                'vote'    => $vote,
            ]);
        }

        // Голос — не изменение контента: обновляем рейтинг через query builder,
        // чтобы не порождать событие updated (FeedableTrait иначе перезаписывал бы
        // feeds и сбрасывал кеш ленты на каждый голос)
        $query = $post->newQuery()->whereKey($post->getKey());
        $vote === '+' ? $query->increment('rating') : $query->decrement('rating');
        $post->refresh();

        return [
            'success' => true,
            'cancel'  => $isCancel,
            'post'    => $post,
            // Голос после операции: голос в другую сторону снимает прежний
            'vote' => $isCancel ? null : $vote,
        ];
    }

    /**
     * Связь голоса пользователя (morph-имя relate единое по движку)
     *
     * @return MorphOne<Poll, Model>
     */
    private function pollRelation(Model $post, User $user): MorphOne
    {
        return $post->morphOne(Poll::class, 'relate')
            ->where('user_id', $user->id);
    }
}

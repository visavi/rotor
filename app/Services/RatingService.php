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
     * выбирает вызывающий — сайту нужен перерисованный блок, API — числа
     *
     * @return array{success: bool, message?: string, cancel?: bool, post?: Model, vote?: string|null}
     */
    public function vote(User $user, ?string $type, int $id, ?string $vote): array
    {
        if (! in_array($type, self::types(), true)) {
            return ['success' => false, 'message' => 'Type invalid'];
        }

        if (! in_array($vote, ['+', '-'], true)) {
            return ['success' => false, 'message' => 'Invalid rating'];
        }

        /** @var class-string<Model> $model */
        $model = Relation::getMorphedModel($type);
        $post = $model::query()
            ->where('id', $id)
            ->where('user_id', '<>', $user->id)
            ->first();

        if (! $post) {
            return ['success' => false, 'message' => __('main.record_not_found')];
        }

        $poll = $this->pollRelation($post, $user)->firstOrNew();
        $isCancel = false;

        if ($poll->exists) {
            if ($poll->vote === $vote) {
                return ['success' => false];
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
            // Голос после операции: повторный клик по своей стрелке его снимает
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

<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Auth\Access\Response;

/**
 * Права на комментарии всех разделов на сайте и в API
 */
class CommentPolicy
{
    public function __construct(private readonly CommentService $comments)
    {
    }

    /**
     * Свой комментарий — первые минуты после публикации и пока обсуждение открыто
     */
    public function update(User $user, Comment $comment): Response
    {
        return match (true) {
            $comment->user_id !== $user->id                  => Response::deny(__('main.comment_deleted')),
            ! $this->comments->editable($comment)            => Response::deny(__('main.editing_impossible')),
            (bool) $comment->relate?->getAttribute('closed') => Response::deny(__('main.closed_comments')),
            default                                          => Response::allow(),
        };
    }

    /**
     * Удаляет только администрация: автору остаётся правка
     */
    public function delete(User $user): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny(__('main.deleted_only_admins'));
    }
}

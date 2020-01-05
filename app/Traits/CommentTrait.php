<?php

declare(strict_types=1);

namespace App\Traits;

use App\Classes\Validator;
use App\Models\BaseModel;
use App\Models\Comment;
use App\Models\Flood;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait CommentTrait
{
    /**
     * Create comment
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     *
     * @return Model
     */
    public function createComment(Request $request, Validator $validator, Flood $flood): ?Model
    {
        if ($request->isMethod('post')) {
            $text    = check($request->input('msg'));
            $token   = check($request->input('token'));

            $validator
                ->true(getUser(), __('main.not_authorized'))
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($text, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->empty($this->closed, ['msg' => __('main.closed_comments')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $comment = Comment::query()->create([
                    'relate_id'   => $this->id,
                    'relate_type' => static::class,
                    'text'        => $text,
                    'user_id'     => getUser('id'),
                    'created_at'  => SITETIME,
                    'ip'          => getIp(),
                    'brow'        => getBrowser(),
                ]);

                $user = getUser();
                $user->increment('allcomments');
                $user->increment('point');
                $user->increment('money', 5);

                $this->increment('count_comments');

                $flood->saveState();

                setFlash('success', __('main.comment_added_success'));
                return $comment;
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return null;
    }

    /**
     * Get comment
     *
     * @param int $commentId
     *
     * @return Model
     */
    public function getComment(int $commentId): ?Model
    {
        $comment = Comment::query()
            ->where('relate_type', static::class)
            ->where('comments.id', $commentId)
            ->where('comments.user_id', getUser('id'))
            ->first();

        if (! $comment) {
            abort('default', __('main.comment_deleted'));
        }

        if ($comment->created_at + 600 < SITETIME) {
            abort('default', __('main.editing_impossible'));
        }

        return $comment;
    }

    /**
     * Edit comment
     *
     * @param Model     $comment
     * @param Request   $request
     * @param Validator $validator
     *
     * @return Model
     */
    public function editComment(?Model $comment, Request $request, Validator $validator): ?Model
    {
        if ($comment && $request->isMethod('post')) {
            $text  = check($request->input('msg'));
            $token = check($request->input('token'));

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($text, 5, setting('comment_length'), ['msg' => __('validator.text')])
                ->empty($this->closed, ['msg' => __('main.closed_comments')]);

            if ($validator->isValid()) {
                $text = antimat($text);

                $comment->update([
                    'text' => $text,
                ]);

                setFlash('success', __('main.comment_edited_success'));
                return $comment;
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        return null;
    }

    /**
     * Redirect last page
     *
     * @param int $id
     */
/*    public function end(int $id): void
    {
        $model = BaseModel::query()->find($id);

        if (! $model) {
            abort(404, __('photos.photo_not_exist'));
        }

        $total = Comment::query()
            ->where('relate_type', static::class)
            ->where('relate_id', $this->id)
            ->count();

        $end = ceil($total / setting('postgallery'));
        redirect('/photos/comments/' . $this->id . '?page=' . $end);
    }*/

}

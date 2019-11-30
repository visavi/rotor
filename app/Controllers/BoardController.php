<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Classes\Validator;
use App\Models\Board;
use App\Models\File;
use App\Models\Flood;
use App\Models\Item;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BoardController extends BaseController
{
    /**
     * Главная страница
     *
     * @param int $id
     * @return string
     */
    public function index(int $id = null): string
    {
        $board = null;

        if ($id) {
            /** @var Board $board */
            $board = Board::query()->find($id);

            if (! $board) {
                abort(404, __('boards.category_not_exist'));
            }
        }

        $items = Item::query()
            ->when($board, static function (Builder $query) use ($board) {
                return $query->where('board_id', $board->id);
            })
            ->where('expires_at', '>', SITETIME)
            ->orderByDesc('updated_at')
            ->with('category', 'user', 'files')
            ->paginate(Item::BOARD_PAGINATE);

        $boards = Board::query()
            ->where('parent_id', $board->id ?? 0)
            ->get();

        return view('boards/index', compact('items', 'board', 'boards'));
    }

    /**
     * Просмотр объявления
     *
     * @param int $id
     * @return string
     */
    public function view(int $id): string
    {
        /** @var Item $item */
        $item = Item::query()
            ->with('category')
            ->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        if ($item->expires_at <= SITETIME && $item->user_id !== getUser('id')) {
            abort('default', __('boards.item_not_active'));
        }

        return view('boards/view', compact('item'));
    }

    /**
     * Создание объявления
     *
     * @param Request   $request
     * @param Validator $validator
     * @param Flood     $flood
     * @return string
     */
    public function create(Request $request, Validator $validator, Flood $flood): string
    {
        $bid = int($request->input('bid'));

        if (! $user = getUser()) {
            abort(403);
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        if ($boards->isEmpty()) {
            abort('default', __('boards.categories_not_created'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $price = int($request->input('price'));
            $phone = preg_replace('/\D/', '', $request->input('phone'));

            /** @var Board $board */
            $board = Board::query()->find($bid);

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => __('validator.phone')], false)
                ->false($flood->isFlood(), ['msg' => __('validator.flood', ['sec' => $flood->getPeriod()])])
                ->notEmpty($board, ['category' => __('boards.category_not_exist')]);

            if ($board) {
                $validator->empty($board->closed, ['category' => __('boards.category_closed')]);
            }

            if ($validator->isValid()) {
                /** @var Item $item */
                $item = Item::query()->create([
                    'board_id'   => $board->id,
                    'title'      => $title,
                    'text'       => $text,
                    'user_id'    => $user->id,
                    'price'      => $price,
                    'phone'      => $phone,
                    'created_at' => SITETIME,
                    'updated_at' => SITETIME,
                    'expires_at' => strtotime('+1 month', SITETIME),
                ]);

                $item->category->increment('count_items');

                File::query()
                    ->where('relate_type', Item::class)
                    ->where('relate_id', 0)
                    ->where('user_id', getUser('id'))
                    ->update(['relate_id' => $item->id]);

                clearCache(['statBoards', 'recentBoards']);
                $flood->saveState();

                setFlash('success', __('boards.item_success_added'));
                redirect('/items/' . $item->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $files = File::query()
            ->where('relate_type', Item::class)
            ->where('relate_id', 0)
            ->where('user_id', getUser('id'))
            ->get();

        return view('boards/create', compact('boards', 'bid', 'files'));
    }

    /**
     * Редактирование объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @return string
     */
    public function edit(int $id, Request $request, Validator $validator): string
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        if ($request->isMethod('post')) {
            $token = check($request->input('token'));
            $bid   = int($request->input('bid'));
            $title = check($request->input('title'));
            $text  = check($request->input('text'));
            $price = check($request->input('price'));
            $phone = preg_replace('/\D/', '', $request->input('phone'));

            /** @var Board $board */
            $board = Board::query()->find($bid);

            $validator
                ->equal($token, $_SESSION['token'], __('validator.token'))
                ->length($title, 5, 50, ['title' => __('validator.text')])
                ->length($text, 50, 5000, ['text' => __('validator.text')])
                ->regex($phone, '#^\d{11}$#', ['phone' => __('validator.phone')], false)
                ->notEmpty($board, ['category' => __('boards.category_not_exist')])
                ->equal($item->user_id, getUser('id'), __('boards.item_not_author'));

            if ($board) {
                $validator->empty($board->closed, ['category' => __('boards.category_closed')]);
            }

            if ($validator->isValid()) {
                // Обновление счетчиков
                if ($item->board_id !== $board->id) {
                    $board->increment('count_items');
                    Board::query()->where('id', $item->board_id)->decrement('count_items');
                }

                $item->update([
                    'board_id' => $board->id,
                    'title'    => $title,
                    'text'     => $text,
                    'price'    => $price,
                    'phone'    => $phone,
                ]);

                clearCache(['statBoards', 'recentBoards']);
                setFlash('success', __('boards.item_success_edited'));
                redirect('/items/' . $item->id);
            } else {
                setInput($request->all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $boards = Board::query()
            ->where('parent_id', 0)
            ->with('children')
            ->orderBy('sort')
            ->get();

        return view('boards/edit', compact('item', 'boards'));
    }

    /**
     * Снятие / Публикация объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     */
    public function close(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->equal($item->user_id, getUser('id'), __('boards.item_not_author'));

        if ($validator->isValid()) {
            if ($item->expires_at > SITETIME) {
                $status = __('boards.item_success_unpublished');
                $item->update([
                    'expires_at' => SITETIME,
                ]);

                $item->category->decrement('count_items');
            } else {
                $status  = __('boards.item_success_published');
                $expired = strtotime('+1 month', $item->updated_at) <= SITETIME;

                $item->update([
                    'expires_at' => strtotime('+1 month', SITETIME),
                    'updated_at' => $expired ? SITETIME : $item->updated_at,
                ]);

                $item->category->increment('count_items');
            }

            setFlash('success', $status);
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/items/edit/' . $item->id);
    }

    /**
     * Удаление объявления
     *
     * @param int       $id
     * @param Request   $request
     * @param Validator $validator
     * @throws Exception
     */
    public function delete(int $id, Request $request, Validator $validator): void
    {
        $token = check($request->input('token'));

        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        /** @var Item $item */
        $item = Item::query()->find($id);

        if (! $item) {
            abort(404, __('boards.item_not_exist'));
        }

        $validator->equal($token, $_SESSION['token'], __('validator.token'))
            ->equal($item->user_id, getUser('id'), __('boards.item_not_author'));

        if ($validator->isValid()) {
            $item->delete();

            $item->category->decrement('count_items');

            clearCache(['statBoards', 'recentBoards']);
            setFlash('success', __('boards.item_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect('/boards/' . $item->board_id);
    }

    /**
     * Мои объявления
     *
     * @return string
     */
    public function active(): string
    {
        if (! getUser()) {
            abort(403, __('main.not_authorized'));
        }

        $items = Item::query()
            ->where('user_id', getUser('id'))
            ->orderByDesc('updated_at')
            ->with('category', 'user', 'files')
            ->paginate(Item::BOARD_PAGINATE);

        return view('boards/active', compact('items'));
    }
}

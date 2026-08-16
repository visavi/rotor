<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Http\Resources\UserResource;
use App\Models\Online;
use App\Models\User;
use App\Traits\HandlesApiPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Списки пользователей
 */
class UserApiController extends Controller
{
    use HandlesApiPagination;

    /**
     * Рейтинг пользователей
     */
    public function index(Request $request): JsonResource
    {
        $type = $request->input('type', 'users');
        $search = trim((string) $request->input('user', ''));

        // Сортировка та же, что на сайте: point, rating, money, created, updated
        [, $orderBy] = User::getSorting($request->input('sort', 'point'), $this->apiOrder($request, 'desc'));

        $users = User::query()
            ->when($type === 'admins', static fn (Builder $q) => $q->whereIn('level', User::ADMIN_GROUPS))
            ->when(
                $type === 'birthdays',
                static fn (Builder $q) => $q->whereRaw('substr(birthday, 1, 5) = ?', now()->format('d.m')),
            )
            ->when($search !== '', static fn (Builder $q) => $q->where(
                static fn (Builder $q) => $q->where('login', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%'),
            ))
            ->orderBy(...$orderBy)
            ->orderBy('id')
            ->paginate($this->apiPerPage($request, (int) setting('userlist')))
            ->appends($request->query());

        return UserResource::collection($users);
    }

    /**
     * Подсказка логинов для упоминаний
     */
    public function search(Request $request): JsonResource
    {
        $query = (string) $request->input('query', '');

        if (mb_strlen($query) < 2) {
            return AuthorResource::collection([]);
        }

        $users = User::query()
            ->where(static function (Builder $q) use ($query) {
                $q->where('login', 'like', $query . '%')
                    ->orWhere('name', 'like', $query . '%');
            })
            ->orderByDesc('point')
            ->limit(10)
            ->get();

        return AuthorResource::collection($users);
    }

    /**
     * Кто сейчас на сайте
     *
     * Гости считаются, но списком не отдаются: у них нет ничего, кроме ip
     */
    public function online(Request $request): JsonResponse
    {
        $online = $this->lastVisits()
            ->select('o1.*')
            ->whereNotNull('o1.user_id')
            ->with('user')
            ->orderByDesc('updated_at')
            ->paginate($this->apiPerPage($request, (int) setting('onlinelist')));

        $guests = $this->lastVisits()->whereNull('o1.user_id')->count();

        return response()->json([
            'data' => AuthorResource::collection(
                $online->getCollection()->pluck('user')->filter()->values(),
            ),
            'meta' => [
                'current_page' => $online->currentPage(),
                'last_page'    => $online->lastPage(),
                'per_page'     => $online->perPage(),
                'total'        => $online->total(),
                'users'        => $online->total(),
                'guests'       => $guests,
            ],
        ]);
    }

    /**
     * Последний визит каждого посетителя
     *
     * Заходов с разных браузеров может быть несколько, в списке нужен один
     *
     * @return Builder<Online>
     */
    private function lastVisits(): Builder
    {
        return Online::query()
            ->from('online as o1')
            ->leftJoin('online as o2', static function (JoinClause $join) {
                $join->on('o1.user_id', 'o2.user_id')
                    ->on('o1.updated_at', '<', 'o2.updated_at');
            })
            ->whereNull('o2.updated_at');
    }
}

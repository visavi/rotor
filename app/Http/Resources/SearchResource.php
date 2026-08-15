<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Search;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Найденная запись поискового индекса
 *
 * @mixin Search
 */
class SearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $relate = $this->relate;

        return [
            'type'    => $this->relate_type,
            'id'      => $this->relate_id,
            'section' => SearchService::types()[$this->relate_type] ?? null,
            'title'   => $this->resolveTitle($relate),
            // Запись могли удалить, индекс чистится обработчиком модели, но не при удалении пачкой
            'url'         => $this->resolveUrl($relate),
            'breadcrumbs' => $relate && method_exists($relate, 'getBreadcrumbs') ? $relate->getBreadcrumbs() : [],
            'text'        => $relate?->getAttribute('text') ? absolutizeUrls($relate->getAttribute('text')) : null,
            // У пользователя автор и есть сама найденная запись
            'user'       => $this->resolveUser($relate),
            'created_at' => dateFixed($this->created_at, 'c', true),
        ];
    }

    /**
     * Заголовок записи, у пользователя — логин, у комментария — запись, к которой он оставлен
     */
    private function resolveTitle(?Model $relate): ?string
    {
        if (! $relate) {
            return null;
        }

        if ($relate instanceof User) {
            return $relate->login;
        }

        return $relate->getAttribute('title')
            ?? $relate->getAttribute('relate')?->getAttribute('title');
    }

    /**
     * Ссылка на найденную запись, у пользователя — на профиль
     */
    private function resolveUrl(?Model $relate): ?string
    {
        if ($relate instanceof User) {
            return route('users.user', ['login' => $relate->login]);
        }

        return $relate && method_exists($relate, 'getViewUrl') ? $relate->getViewUrl() : null;
    }

    /**
     * Автор найденной записи
     */
    private function resolveUser(?Model $relate): ?AuthorResource
    {
        if ($relate instanceof User) {
            return AuthorResource::make($relate);
        }

        return $relate?->getAttribute('user') ? AuthorResource::make($relate->getAttribute('user')) : null;
    }
}

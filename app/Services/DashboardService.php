<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Error;
use App\Models\User;
use App\Support\Registry;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardService
{
    /** Глубина графиков в днях */
    public const DAYS = 7;

    /** Сколько живёт кэш виджетов */
    public const TTL = 600;

    /** Настройка с составом и порядком виджетов */
    public const SETTING = 'dashboard_widgets';

    /** Чем добираются необязательные ключи виджета */
    private const DEFAULTS = [
        'previous' => 0,
        'days'     => null,
        'icon'     => 'fas fa-chart-line',
        'color'    => '#0d6efd',
        'type'     => 'line',
        'url'      => null,
        'level'    => User::EDITOR,
        'inverse'  => false,
        'unit'     => null,
    ];

    /**
     * Возвращает все виджеты, включая выключенные
     *
     * Нужен странице настроек: там показывают полный список, поэтому данные
     * считаются даже для скрытых виджетов. Заходят туда редко
     *
     * @return array<string, array<string, mixed>>
     */
    public function declarations(): array
    {
        $widgets = [];

        foreach ($this->handlers() as $key => $handler) {
            if ($widget = $this->build($key, $handler['handler'])) {
                $widgets[$key] = $widget;
            }
        }

        return $widgets;
    }

    /**
     * Собирает виджеты для главной страницы админки
     *
     * @return array<int, array<string, mixed>>
     */
    public function widgets(): array
    {
        $settings = self::settings();

        $widgets = Cache::remember('dashboard-widgets-' . md5(setting(self::SETTING) ?? ''), self::TTL, function () use ($settings) {
            $widgets = [];

            foreach ($this->handlers() as $key => $handler) {
                // Ключа в настройке нет — модуль только поставили, виджет показываем
                if (($settings[$key] ?? true) === false) {
                    continue;
                }

                if ($widget = $this->build($key, $handler['handler'])) {
                    $widgets[] = $widget;
                }
            }

            return $widgets;
        });

        // Уровень проверяется после кэша: он общий, а видят панель админы разных прав
        return array_values(array_filter($widgets, static fn ($widget) => isAdmin($widget['level'])));
    }

    /**
     * Колбэки всех виджетов в порядке вывода
     *
     * Сперва настроенные админом, следом новые — модуль поставили, а до
     * настроек ещё не дошли, и виджет не должен пропасть без следа.
     * Ключи ядра идут первыми, но модуль вправе занять любой из них:
     * так раздел подменяет ядровый виджет своим
     *
     * @return array<string, array<string, mixed>>
     */
    private function handlers(): array
    {
        $handlers = [
            'registrations' => ['handler' => $this->registrations(...), 'priority' => 0],
            'comments'      => ['handler' => $this->comments(...), 'priority' => 0],
            'errors'        => ['handler' => $this->errors(...), 'priority' => 0],
            ...Registry::$widgets,
        ];

        uasort($handlers, static fn ($a, $b) => $b['priority'] <=> $a['priority']);

        $order = array_flip(array_keys(self::settings()));

        uksort($handlers, static fn ($a, $b) => ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX));

        return $handlers;
    }

    /**
     * Готовит виджет к выводу
     *
     * @return array<string, mixed>|null
     */
    private function build(string $key, callable $handler): ?array
    {
        $widget = $this->call($key, $handler);

        if (! $widget || ! isset($widget['label'], $widget['value'], $widget['series'])) {
            return null;
        }

        $widget += self::DEFAULTS;

        // Виджет вправе жить своим периодом — плитка подписывает именно его
        $widget['days'] ??= self::days();

        $widget['series'] = self::normalize($widget['series'], $widget['color']);

        return self::withDiff($widget);
    }

    /**
     * Вызывает колбэк модуля, не давая ему уронить панель
     *
     * @return array<string, mixed>|null
     */
    private function call(string $key, callable $handler): ?array
    {
        try {
            // Период передаётся аргументом: колбэк, который его не ждёт, работает как прежде
            return $handler(self::days()) ?: null;
        } catch (Throwable $e) {
            // Битый виджет не должен ронять панель — пропускаем, остальные рисуются штатно.
            // Лог подавляем: одна запись на виджет в час, иначе ошибка пишется на каждый запрос.
            if (Cache::add('widget_error_' . $key, true, 3600)) {
                report($e);
            }

            return null;
        }
    }

    /**
     * Состав и порядок виджетов, заданные админом
     *
     * Хранятся все известные ключи по порядку, выключенные с минусом: иначе
     * скрытый админом виджет не отличить от виджета только что поставленного
     * модуля, который показать как раз нужно
     *
     * @return array<string, bool> ['ключ' => показывать ли]
     */
    public static function settings(): array
    {
        $settings = [];

        foreach (array_filter(explode(',', (string) setting(self::SETTING))) as $key) {
            $settings[ltrim($key, '-')] = ! str_starts_with($key, '-');
        }

        return $settings;
    }

    /**
     * Виджет регистраций
     *
     * @return array<string, mixed>
     */
    private function registrations(int $days): array
    {
        return [
            'label' => __('index.widget_registrations'),
            'icon'  => 'fas fa-user-plus',
            'color' => '#198754',
            'type'  => 'bar',
            'url'   => route('users.index'),
            ...self::trend(User::query(), $days),
        ];
    }

    /**
     * Виджет комментариев
     *
     * @return array<string, mixed>
     */
    private function comments(int $days): array
    {
        return [
            'label' => __('index.widget_comments'),
            'icon'  => 'fas fa-comments',
            'color' => '#fd7e14',
            'type'  => 'line',
            ...self::trend(Comment::query(), $days),
        ];
    }

    /**
     * Глубина графиков в днях
     *
     * Виджеты получают её аргументом и не смотрят на константу: если период
     * станет настройкой, менять придётся только этот метод, а не модули
     */
    public static function days(): int
    {
        return self::DAYS;
    }

    /**
     * Готовит данные графика: ряд за текущий период и итог за предыдущий
     *
     * @return array{value: int, series: array<int, int>, previous: int}
     */
    public static function trend(Builder $query, ?int $days = null, string $column = 'created_at', ?string $sum = null): array
    {
        $days ??= self::days();

        // Оба периода берутся одним запросом: с прошлым сравнивают все виджеты
        $series = self::dailySeries($query, $days * 2, $column, $sum);

        $previous = array_slice($series, 0, $days);
        $current = array_slice($series, $days);

        return [
            'value'    => array_sum($current),
            'series'   => array_values($current),
            'previous' => array_sum($previous),
        ];
    }

    /**
     * Виджет ошибок: не найдено, запрещено и автобаны в одной плитке
     *
     * @return array<string, mixed>
     */
    private function errors(int $days): array
    {
        return [
            'label' => __('index.widget_errors'),
            'icon'  => 'fas fa-bug',
            'color' => '#dc3545',
            'type'  => 'line',
            'url'   => route('admin.errors.index'),
            'level' => User::ADMIN,
            // Рост ошибок — плохая новость, поэтому окраска процента обратная
            'inverse' => true,
            ...self::trends([
                ['label' => '404', 'color' => '#dc3545', 'query' => Error::query()->where('code', 404)],
                ['label' => '403', 'color' => '#fd7e14', 'query' => Error::query()->where('code', 403)],
                // 666 — код автобанов, своей таблицы у них нет
                ['label' => __('admin.errors.autobans'), 'color' => '#6f42c1', 'query' => Error::query()->where('code', 666)],
            ], $days),
        ];
    }

    /**
     * Готовит данные графика из нескольких источников
     *
     * Итог виджета — сумма источников, каждый рисуется своей линией и попадает
     * в легенду. Источник: ['label' => string, 'color' => string,
     * 'query' => Builder, 'sum' => ?string]
     *
     * @param array<int, array<string, mixed>> $sources
     *
     * @return array{value: int, series: array<int, array<string, mixed>>, previous: int}
     */
    public static function trends(array $sources, ?int $days = null): array
    {
        $days ??= self::days();

        $value = 0;
        $previous = 0;
        $series = [];

        foreach ($sources as $source) {
            $trend = self::trend($source['query'], $days, $source['column'] ?? 'created_at', $source['sum'] ?? null);

            $value += $trend['value'];
            $previous += $trend['previous'];

            $series[] = [
                'label'  => $source['label'],
                'color'  => $source['color'],
                'values' => $trend['series'],
            ];
        }

        return compact('value', 'series', 'previous');
    }

    /**
     * Считает количество записей по дням
     *
     * Дни без записей заполняются нулями, иначе график сжимается и врёт
     *
     * Столбец $sum суммирует значения вместо подсчёта записей — так считаются
     * деньги. Имена столбцов приходят из кода модуля, не от пользователя
     *
     * @return array<string, int> ['Y-m-d' => count]
     */
    public static function dailySeries(Builder $query, int $days, string $column = 'created_at', ?string $sum = null): array
    {
        $total = $sum ? 'COALESCE(SUM(' . $sum . '), 0)' : 'COUNT(*)';

        $rows = $query
            ->selectRaw('DATE(' . $column . ') AS day, ' . $total . ' AS total')
            ->where($column, '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy(DB::raw('DATE(' . $column . ')'))
            ->pluck('total', 'day')
            ->all();

        $series = [];
        foreach (self::dates($days) as $day) {
            $series[$day] = (int) ($rows[$day] ?? 0);
        }

        return $series;
    }

    /**
     * Список дат графика от старой к свежей
     *
     * @return array<int, string>
     */
    public static function dates(int $days): array
    {
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[] = now()->subDays($i)->format('Y-m-d');
        }

        return $dates;
    }

    /**
     * Приводит ряд к списку серий
     *
     * Виджет с одной метрикой отдаёт плоский массив чисел, совмещённый —
     * готовые серии. Компонент работает только со вторым видом
     *
     * @param array<int, mixed> $series
     *
     * @return array<int, array<string, mixed>>
     */
    private static function normalize(array $series, string $color): array
    {
        if (isset($series[0]) && is_array($series[0])) {
            return $series;
        }

        return [['label' => null, 'color' => $color, 'values' => array_values($series)]];
    }

    /**
     * Дополняет виджет процентом роста к прошлому периоду
     *
     * На пустом прошлом периоде процент не считается: рост от нуля бесконечен
     *
     * @param array<string, mixed> $widget
     *
     * @return array<string, mixed>
     */
    private static function withDiff(array $widget): array
    {
        $previous = (int) ($widget['previous'] ?? 0);

        $widget['diff'] = $previous > 0
            ? round(($widget['value'] - $previous) / $previous * 100, 1)
            : null;

        return $widget;
    }
}

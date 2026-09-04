<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Датирована раньше всех *_to_datetime миграций намеренно: те дропают индексы
 * перед подменой колонок и падали с 1091 на базах, где индекса не было.
 */
return new class extends Migration {
    /**
     * Обычные индексы из create-миграций ядра и модулей: базы, пережившие переезд
     * со старого движка, никогда не выполняли Schema::create и остались без части
     * индексов. Таблицы модулей перечислены здесь же — рассылать по модулям
     * тридцать одинаковых миграций дороже, а hasTable() отсеет неустановленные.
     *
     * Индексы по датам сюда не входят: *_to_datetime сами навешивают их заново
     * после подмены колонок.
     */
    private array $indexes = [
        'article_tags' => [['article_id'], ['tag_id']],
        'articles'     => [['category_id'], ['user_id']],
        'banhist'      => [['user_id']],
        'blacklist'    => [['type'], ['value']],
        'bookmarks'    => [['topic_id'], ['user_id']],
        'comments'     => [['parent_id'], ['relate_type', 'relate_id']],
        'dialogues'    => [['user_id', 'author_id']],
        'downs'        => [['category_id']],
        'errors'       => [['code', 'created_at']],
        'files'        => [['relate_type', 'relate_id'], ['user_id']],
        'floods'       => [['uid']],
        'items'        => [['board_id']],
        'jobs'         => [['queue']],
        'logs'         => [['created_at']],
        'lottery'      => [['day']],
        'offers'       => [['rating']],
        'orders'       => [['user_id', 'created_at'], ['token'], ['payment_id'], ['created_at']],
        'photos'       => [['user_id']],
        'polls'        => [['relate_type', 'relate_id', 'user_id']],
        'rating'       => [['user_id']],
        'readers'      => [['relate_type', 'relate_id', 'ip']],
        'search'       => [['created_at'], ['relate_type', 'created_at']],
        'socials'      => [['user_id']],
        'spam'         => [['relate_type', 'relate_id']],
        'status'       => [['point'], ['topoint']],
        'stickers'     => [['code']],
        'surprise'     => [['user_id']],
        'transfers'    => [['user_id'], ['recipient_id']],
        'users'        => [['level'], ['point'], ['money'], ['rating']],
        'walls'        => [['user_id']],
    ];

    /**
     * Уникальные индексы навешиваются только при отсутствии дублей:
     * упасть на 1062 посреди обновления хуже, чем остаться без индекса.
     */
    private array $uniques = [
        'ban'               => [['ip']],
        'counters24'        => [['period']],
        'counters31'        => [['period']],
        'email_changes'     => [['email']],
        'feeds'             => [['relate_type', 'relate_id']],
        'module_registries' => [['url']],
        'notebooks'         => [['user_id']],
        'notices'           => [['type']],
        'search'            => [['relate_type', 'relate_id']],
        'socials'           => [['provider', 'provider_id']],
        'tags'              => [['name']],
        'user_data'         => [['user_id', 'field_id']],
        'users'             => [['login'], ['email']],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            foreach ($indexes as $columns) {
                if (! $this->isMissing($table, $columns, 'index')) {
                    continue;
                }

                Schema::table($table, static fn (Blueprint $blueprint) => $blueprint->index($columns));
            }
        }

        foreach ($this->uniques as $table => $indexes) {
            foreach ($indexes as $columns) {
                if (! $this->isMissing($table, $columns, 'unique') || $this->hasDuplicates($table, $columns)) {
                    continue;
                }

                Schema::table($table, static fn (Blueprint $blueprint) => $blueprint->unique($columns));
            }
        }
    }

    public function down(): void
    {
        // Индексы восстанавливают схему до состояния create-миграций — откатывать нечего
    }

    private function isMissing(string $table, array $columns, string $type): bool
    {
        if (! Schema::hasTable($table)) {
            return false;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        // Без типа hasIndex() ловит любой индекс по этим колонкам, в том числе unique
        return $type === 'unique'
            ? ! Schema::hasIndex($table, $columns, 'unique')
            : ! Schema::hasIndex($table, $columns);
    }

    private function hasDuplicates(string $table, array $columns): bool
    {
        // NULL в unique не конфликтуют — строки с ними в подсчёт дублей не идут
        $query = DB::table($table)->select($columns);

        foreach ($columns as $column) {
            $query->whereNotNull($column);
        }

        return $query->groupBy($columns)->havingRaw('count(*) > 1')->exists();
    }
};

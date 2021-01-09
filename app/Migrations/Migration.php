<?php

declare(strict_types=1);

namespace App\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Builder;
use PDO;
use Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration
{
    /** @var DB $db */
    public $db;

    /** @var Builder $capsule */
    public $schema;

    public function init(): void
    {
        $this->db = new DB();
        $this->db->addConnection([
            'driver'    => config('DB_DRIVER'),
            'port'      => config('DB_PORT'),
            'host'      => config('DB_HOST'),
            'database'  => config('DB_DATABASE'),
            'username'  => config('DB_USERNAME'),
            'password'  => config('DB_PASSWORD'),
            'charset'   => config('DB_CHARSET'),
            'collation' => config('DB_COLLATION'),
            'prefix'    => config('DB_PREFIX'),
            'engine'    => config('DB_ENGINE'),
            'options' => [
                PDO::ATTR_PERSISTENT => true
            ]
        ]);

        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $this->schema = $this->db::schema();
    }
}

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
        $this->db->addConnection(array_merge(
            config('database.connections.' . config('database.default')),
            [
                'options' => [
                    PDO::ATTR_PERSISTENT => true
                ],
            ]
        ));

        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $this->schema = $this->db::schema();
    }
}

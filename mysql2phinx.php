<?php
/**
 * Simple command line tool for creating phinx migration code from an existing MySQL database.
 *
 * Commandline usage:
 * ```
 * $ php -f mysql2phinx [database] [user] [password] > migration.php
 * ```
 */

if ($argc < 4) {
    echo '===============================' . PHP_EOL;
    echo 'Phinx MySQL migration generator' . PHP_EOL;
    echo '===============================' . PHP_EOL;
    echo 'Usage:' . PHP_EOL;
    echo 'php -f ' . $argv[0] . ' [database] [user] [password] > migration.php';
    echo PHP_EOL;
    exit;
}

$config = array(
    'name'    => $argv[1],
    'user'    => $argv[2],
    'pass'    => $argv[3],
    'host'    => $argc === 5 ? $argv[6] : 'localhost',
    'port'    => $argc === 6 ? $argv[5] : '3306'
);

function createMigration($mysqli, $indent = 2)
{
    $output = array();
    foreach (getTables($mysqli) as $table) {
        $output[] = getTableMigration($table, $mysqli, $indent);
    }
    return implode(PHP_EOL, $output) . PHP_EOL ;
}

function getMysqliConnection($config)
{
    return new mysqli($config['host'], $config['user'], $config['pass'], $config['name']);
}

function getTables($mysqli)
{
    $res = $mysqli->query('SHOW TABLES');
    return array_map(function($a) { return $a[0]; }, $res->fetch_all());
}

function getTableMigration($table, $mysqli, $indent)
{
    $ind = getIndentation($indent);

    $output = array();
    $output[] = $ind . '// Migration for table ' . $table;
    $output[] = $ind . '$table = $this->table(\'' . $table . '\', [\'collation\' => \'utf8mb4_unicode_ci\']);';
    $output[] = $ind . '$table';

    foreach (getColumns($table, $mysqli) as $column) {
        if ($column['Field'] !== 'id') {
            $output[] = getColumnMigration($column['Field'], $column, $indent + 1);
        }
    }

    if ($keys = getIndexMigrations(getIndexes($table, $mysqli), $indent + 1)){
        $output[] = $keys;
    }

    if ($foreign_keys = getForeignKeysMigrations(getForeignKeys($table, $mysqli), $indent + 1)) {
        $output[] = $foreign_keys;
    }

    $output[] = $ind . '    ->create();';
    $output[] = PHP_EOL;

    return implode(PHP_EOL, $output);
}

function getColumnMigration($column, $columndata, $indent)
{
    $ind = getIndentation($indent);

    $phinxtype = getPhinxColumnType($columndata);
    $columnattributes = getPhinxColumnAttibutes($phinxtype, $columndata);

    $opt = ($columnattributes != "[]") ? ', '.$columnattributes : '';

    $output = $ind . '->addColumn(\'' . $column . '\', \'' . $phinxtype . '\'' . $opt . ')';
    return $output;
}

function getIndexMigrations($indexes, $indent)
{
    $ind = getIndentation($indent);

    $keyedindexes = array();
    foreach($indexes as $index) {
        if ($index['Column_name'] === 'id') {
            continue;
        }

        $key = $index['Key_name'];
        if (!isset($keyedindexes[$key])) {
            $keyedindexes[$key] = array();
            $keyedindexes[$key]['columns'] = array();
            $keyedindexes[$key]['unique'] = $index['Non_unique'] !== '1';
            $keyedindexes[$key]['fulltext'] = $index['Index_type'] === 'FULLTEXT';
        }

        $keyedindexes[$key]['columns'][] = $index['Column_name'];
    }

    $output = [];

    foreach ($keyedindexes as $key_name=>$index) {

        if (count($index['columns']) > 1) {
            $columns = "['" . implode("', '", $index['columns']) . "']";
        } else {
            $columns = "'" . current($index['columns']) . "'";
        }
        $needs_comma = false;
        $options = [];
        $options[] = '[';
        // Support for unique indexes
        if ($index['unique']) {
            $options[] =  "'unique' => true";
            $needs_comma = true;
        }
        // Support for full text indexes
        if ($index['fulltext']) {
            if ($needs_comma === true) {
                $options[] = ', ';
            }
            $options[] = "'type' => 'fulltext'";
            $needs_comma = true;
        }

        if (implode($index['columns']) != $key_name) {
            // Support for named indexes
            if ($needs_comma === true) {
                $options[] = ', ';
            }
            $options[] = "'name' => '" . $key_name . "'";
        }
        $options[] = ']';

        $opt = (implode($options) != "[]") ? ', '.implode($options) : '';

        $output[] = $ind . '->addIndex(' . $columns . $opt . ')';
    }

    return implode(PHP_EOL, $output);
}

function getForeignKeysMigrations($foreign_keys, $indent)
{
    $ind = getIndentation($indent);
    $output = [];
    foreach ($foreign_keys as $foreign_key) {
        $output[] = $ind . "->addForeignKey('" . $foreign_key['COLUMN_NAME'] . "', '" . $foreign_key['REFERENCED_TABLE_NAME'] . "', '" . $foreign_key['REFERENCED_COLUMN_NAME'] . "', array("
            . "'delete' => '" . str_replace(' ', '_', $foreign_key['DELETE_RULE']) . "',"
            . "'update' => '" . str_replace(' ', '_', $foreign_key['UPDATE_RULE']) . "'"
            . "))";
    }
    return implode(PHP_EOL, $output);
}

/* ---- */

function getMySQLColumnType($columndata)
{
    $type = $columndata['Type'];
    $pattern = '/^[a-z]+/';
    preg_match($pattern, $type, $match);
    return $match[0];
}

function getPhinxColumnType($columndata)
{
    $type = getMySQLColumnType($columndata);

    switch($type) {
        case 'tinyint':
        case 'boolean':
            return 'boolean';
        case 'smallint':
        case 'int':
        case 'mediumint':
            return 'integer';

        case 'timestamp':
            return 'timestamp';

        case 'date':
            return 'date';

        case 'datetime':
            return 'datetime';

        case 'enum':
            return 'enum';

        case 'char':
            return 'char';

        case 'text':
        case 'tinytext':
            return 'text';

        case 'varchar':
            return 'string';

        default:
            return $type;
    }
}

function getPhinxColumnAttibutes($phinxtype, $columndata)
{
    $attributes = array();

    // var_dump($columndata);

    // limit / length
    $limit = 0;
    switch (getMySQLColumnType($columndata)) {
        case 'bollean':
        case 'tinyint':
            $limit = '1';
            break;

        case 'smallint':
            $limit = 'MysqlAdapter::INT_SMALL';
            break;

        case 'mediumint':
            $limit = 'MysqlAdapter::INT_MEDIUM';
            break;

        case 'bigint':
            $limit = 'MysqlAdapter::INT_BIG';
            break;

        case 'tinytext':
            $limit = 'MysqlAdapter::TEXT_TINY';
            break;

        case 'mediumtext':
            $limit = 'MysqlAdapter::TEXT_MEDIUM';
            break;

        case 'longtext':
            $limit = 'MysqlAdapter::TEXT_LONG';
            break;

        default:
            $pattern = '/\((\d+)\)$/';
            if (1 === preg_match($pattern, $columndata['Type'], $match)) {
                $limit = $match[1];
            }
    }
    if ($limit) {
        if (($phinxtype == 'string' && $limit != 255) || $phinxtype == 'integer' && $limit != 11) {
            $attributes[] = '\'limit\' => ' . $limit;
        }
    }

    // unsigned
    $pattern = '/\(\d+\) unsigned$/';
    if (1 === preg_match($pattern, $columndata['Type'], $match)) {
        $attributes[] = '\'signed\' => false';
    }

    // enum values
    if ($phinxtype === 'enum') {
        $attributes[] = '\'values\' => ' . str_replace('enum', 'array', $columndata['Type']);
    }

    // has NULL
    if ($columndata['Null'] === 'YES') {
        $attributes[] = '\'null\' => true';
    }

    // default value
    if ($columndata['Default'] !== null) {
        $default = is_numeric($columndata['Default']) ? $columndata['Default'] : '\'' . $columndata['Default'] . '\'';
        $attributes[] = '\'default\' => ' . $default;
    }

    // on update CURRENT_TIMESTAMP
    if ($columndata['Extra'] === 'on update CURRENT_TIMESTAMP') {
        $attributes[] = '\'update\' => \'CURRENT_TIMESTAMP\'';
    }

    return '[' . implode(', ', $attributes) . ']';
}

function getColumns($table, $mysqli)
{
    $res = $mysqli->query('SHOW COLUMNS FROM ' . $table);
    return $res->fetch_all(MYSQLI_ASSOC);
}

function getIndexes($table, $mysqli)
{
    $res = $mysqli->query('SHOW INDEXES FROM ' . $table);
    return $res->fetch_all(MYSQLI_ASSOC);
}

function getForeignKeys($table, $mysqli)
{
    $res = $mysqli->query("SELECT
        cols.TABLE_NAME,
        cols.COLUMN_NAME,
        refs.REFERENCED_TABLE_NAME,
        refs.REFERENCED_COLUMN_NAME,
        cRefs.UPDATE_RULE,
        cRefs.DELETE_RULE
    FROM INFORMATION_SCHEMA.COLUMNS as cols
    LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS refs
        ON refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
        AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
        AND refs.TABLE_NAME=cols.TABLE_NAME
        AND refs.COLUMN_NAME=cols.COLUMN_NAME
    LEFT JOIN INFORMATION_SCHEMA.TABLE_CONSTRAINTS AS cons
        ON cons.TABLE_SCHEMA=cols.TABLE_SCHEMA
        AND cons.TABLE_NAME=cols.TABLE_NAME
        AND cons.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
    LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS cRefs
        ON cRefs.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
        AND cRefs.CONSTRAINT_NAME=refs.CONSTRAINT_NAME
    WHERE
        cols.TABLE_NAME = '" . $table . "'
        AND cols.TABLE_SCHEMA = DATABASE()
        AND refs.REFERENCED_TABLE_NAME IS NOT NULL
        AND cons.CONSTRAINT_TYPE = 'FOREIGN KEY'
    ;");
    return $res->fetch_all(MYSQLI_ASSOC);
}

function getIndentation($level)
{
    return str_repeat('    ', $level);
}

echo '<?php' . PHP_EOL;
echo 'use Phinx\Migration\AbstractMigration;' . PHP_EOL;
echo 'use Phinx\Db\Adapter\MysqlAdapter;' . PHP_EOL . PHP_EOL;

echo 'class InitialMigration extends AbstractMigration' . PHP_EOL;
echo '{' . PHP_EOL;
echo '    public function up()' . PHP_EOL;
echo '    {' . PHP_EOL;
echo '        // Automatically created phinx migration commands for tables from database ' . $config['name'] . PHP_EOL . PHP_EOL;
echo createMigration(getMysqliConnection($config));
echo '    }' . PHP_EOL;
echo '}' . PHP_EOL;

<?php
// ---------------------------- Класс для работы с базами данных -------------------------------//
class PDO_ extends PDO {
    static $counter = 0;

    function __construct($dsn, $username, $password) {
        parent::__construct($dsn, $username, $password);
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    function prepare($sql, $params = []) {
        $stmt = parent::prepare($sql, [
                PDO::ATTR_STATEMENT_CLASS => ['PDOStatement_']
        ]);
        return $stmt;
    }

    function query($sql, $params = []) {
        self::$counter++;
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    function querySingle($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn(0);
    }

    function queryFetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    function queryCounter() {
        return self::$counter;
    }
}
// ----------------------------------------------------//
class PDOStatement_ extends PDOStatement {
    function execute($params = []) {
        if (func_num_args() == 1) {
            $params = func_get_arg(0);
        } else {
            $params = func_get_args();
        }
        if (! is_array($params)) {
            $params = [$params];
        }
        parent::execute($params);
        return $this;
    }

    function fetchSingle() {
        return $this->fetchColumn(0);
    }

    function fetchAssoc() {
        $this->setFetchMode(PDO::FETCH_NUM);
        $data = [];
        while ($row = $this->fetch()) {
            $data[$row[0]] = $row[1];
        }
        return $data;
    }
}

// --------------- Класс singleton для подключения к БД -----------------//
class DB {
    private static $instance;
    private function __construct() {}
    private function __clone() {}

    public static function run() {

        if (! isset(self::$instance)) {

            try {
                self::$instance = new PDO_('mysql:host='.env('DB_HOST').';port='.env('DB_PORT').';dbname='.env('DB_DATABASE'), env('DB_USERNAME'), env('DB_PASSWORD'));
                self::$instance->exec('SET CHARACTER SET utf8');
                self::$instance->exec('SET NAMES utf8mb4');
            }

            catch (PDOException $e) {
                die('Connection failed: '.$e->getMessage());
            }
        }
        return self::$instance;
    }

    final public function __destruct() {
        self::$instance = null;
    }
}

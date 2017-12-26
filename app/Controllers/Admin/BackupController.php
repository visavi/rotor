<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\User;
use Illuminate\Database\Capsule\Manager as DB;

class BackupController extends AdminController
{
    public $date;

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();

        if (! isAdmin(User::BOSS)) {
            abort(403, 'Доступ запрещен!');
        }

        if (function_exists('set_time_limit')) {
            set_time_limit(600);
        }

        $this->date = date('d-M-Y_H-i-s', SITETIME);
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $files = glob(STORAGE."/backup/*.{zip,gz,bz2,sql}", GLOB_BRACE);
        arsort($files);

        return view('admin/backup/index', compact('files'));
    }

    /**
     * Создание нового бэкапа
     */
    public function create()
    {
        if (Request::isMethod('post')) {

            $token  = check(Request::input('token'));
            $sheets = check(Request::input('sheets'));
            $method = check(Request::input('method'));
            $level  = (int) Request::input('level');

            $validator = new Validator();
            $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
                ->notEmpty($sheets, ['sheets' => 'Ошибка! Не выбраны таблицы для сохранения!'])
                ->in($method, ['none', 'gzip', 'bzip'], ['method' => 'Ошибка! Неправильный метод сжатия!'])
                ->between($level, 0, 9, ['level' => 'Ошибка! Неправильная степень сжатия!']);

            if ($validator->isValid()) {

                $selectTables = DB::select('SHOW TABLE STATUS where name IN("' . implode('","', $sheets) . '")');

                $limit    = 3000;
                $filename = 'backup_'.$this->date.'.sql';

                $fp = $this->fopen(STORAGE.'/backup/'.$filename, "w", $method, $level);

                foreach ($selectTables as $table) {

                    $show = DB::selectOne("SHOW CREATE TABLE `{$table->Name}`");

                    $this->fwrite($fp, "--\n-- Структура таблицы `{$table->Name}`\n--\n\n", $method);
                    $this->fwrite($fp, "DROP TABLE IF EXISTS `{$table->Name}`;\n{$show->{'Create Table'}};\n\n", $method);

                    $total = DB::table($table->Name)->count();

                    if (! $total) {
                        continue;
                    }

                    $this->fwrite($fp, "--\n-- Дамп данных таблицы `{$table->Name}`\n--\n\n", $method);
                    $this->fwrite($fp, "INSERT INTO `{$table->Name}` VALUES ", $method);

                    for ($i = 0; $i < $total; $i += $limit) {

                        $cols = DB::table($table->Name)->lockForUpdate()->limit($limit)->offset($i)->get();

                        foreach ($cols as $key => $col) {
                            $records = get_object_vars($col);
                            $columns = [];

                            foreach ($records as $record) {
                                $record = str_replace('"', '&quot;', $record);
                                $columns[] = $record ? (is_int($record) ? $record : '"' . $record . '"') : 'NULL';
                            }

                            $this->fwrite($fp, ($key || $i ? ',' : '') . '(' . implode(',', $columns) . ')', $method);
                            unset($columns);
                        }
                        unset($cols);
                    }

                    $this->fwrite($fp, ";\n\n", $method);
                }

                $this->fclose($fp, $method);

                setFlash('success', 'База данных успешно обработана и сохранена!');
                redirect('/admin/backup');
            } else {
                setInput(Request::all());
                setFlash('danger', $validator->getErrors());
            }
        }

        $tables = DB::select('SHOW TABLE STATUS');

        $bzopen = function_exists('bzopen') ? true : false;
        $gzopen = function_exists('gzopen') ? true : false;

        $levels = range(0, 9);

        return view('admin/backup/create', compact('tables', 'bzopen', 'gzopen', 'levels'));
    }

    /**
     * Удаляет сохраненный бэкап
     */
    public function delete()
    {
        $token = check(Request::input('token'));
        $file  = check(Request::input('file'));


        $validator = new Validator();
        $validator->equal($token, $_SESSION['token'], 'Неверный идентификатор сессии, повторите действие!')
            ->notEmpty($file, 'Не передано название бэкапа для удаления!')
            ->regex($file, '|^[\w\.\-]+$|i', 'Недопустимое название бэкапа!')
            ->true(file_exists(STORAGE.'/backup/'.$file), 'Файла для удаления не существует!');

        if ($validator->isValid()) {

            unlink(STORAGE.'/backup/'.$file);

            setFlash('success', 'Бэкап успешно удален!');
        } else {
            setFlash('danger', $validator->getErrors());
        }

        redirect("/admin/backup");
    }

    /**
     * Открывает поток
     */
    private function fopen($name, $mode, $method, $level)
    {
        if ($method === 'bzip') {
            return bzopen($name . '.bz2', $mode);
        }

        if ($method === 'gzip') {
            return gzopen($name . '.gz', "{$mode}b{$level}");
        }

        return fopen($name, $mode . 'b');
    }

    /**
     * Записывает данные в поток
     */
    private function fwrite($fp, $str, $method)
    {
        if ($method === 'bzip') {
            bzwrite($fp, $str);
        } elseif ($method === 'gzip') {
            gzwrite($fp, $str);
        } else {
            fwrite($fp, $str);
        }
    }

    /**
     * Закрывает поток
     */
    private function fclose($fp, $method)
    {
        if ($method === 'bzip') {
            bzclose($fp);
        } elseif ($method === 'gzip') {
            gzclose($fp);
        } else {
            fflush($fp);
            fclose($fp);
        }
    }
}

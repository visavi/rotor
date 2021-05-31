<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Classes\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupController extends AdminController
{
    public $date;

    /**
     * Конструктор
     */
    public function __construct()
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(600);
        }

        $this->date = date('d-M-Y_H-i-s', SITETIME);
    }

    /**
     * Главная страница
     *
     * @return View
     */
    public function index(): View
    {
        $files = glob(storage_path('backups/*.{zip,gz,bz2,sql}'), GLOB_BRACE);
        arsort($files);

        return view('admin/backups/index', compact('files'));
    }

    /**
     * Создание нового бэкапа
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return View|RedirectResponse
     */
    public function create(Request $request, Validator $validator)
    {
        if ($request->isMethod('post')) {
            $sheets = check($request->input('sheets'));
            $method = $request->input('method');
            $level  = int($request->input('level'));

            $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
                ->notEmpty($sheets, ['sheets' => __('admin.backup.no_tables_save')])
                ->in($method, ['none', 'gzip', 'bzip'], ['method' => __('admin.backup.wrong_compression_method')])
                ->between($level, 0, 9, ['level' => __('admin.backup.wrong_compression_ratio')]);

            if ($validator->isValid()) {
                $selectTables = DB::select('SHOW TABLE STATUS where name IN("' . implode('","', $sheets) . '")');

                $limit    = 3000;
                $filename = 'backup_'.$this->date.'.sql';

                $fp = $this->fopen(storage_path('backups/'.$filename), 'w', $method, $level);

                foreach ($selectTables as $table) {
                    $show = DB::selectOne("SHOW CREATE TABLE `{$table->Name}`");
                    $columnsFields = DB::select("SHOW COLUMNS FROM `{$table->Name}`");
                    $columns = '(' .implode(',', array_column($columnsFields, 'Field')) . ')';

                    $this->fwrite($fp, "--\n-- Structure table `{$table->Name}`\n--\n\n", $method);
                    $this->fwrite($fp, "DROP TABLE IF EXISTS `{$table->Name}`;\n{$show->{'Create Table'}};\n\n", $method);

                    $total = DB::table($table->Name)->count();

                    if (! $total) {
                        continue;
                    }

                    $this->fwrite($fp, "--\n-- Dump table `{$table->Name}`\n--\n\n", $method);
                    $this->fwrite($fp, "INSERT INTO `{$table->Name}` {$columns} VALUES ", $method);

                    for ($i = 0; $i < $total; $i += $limit) {
                        $cols = DB::table($table->Name)->lockForUpdate()->limit($limit)->offset($i)->get();

                        foreach ($cols as $key => $col) {
                            $records = get_object_vars($col);
                            $columns = [];

                            foreach ($records as $record) {
                                $record = is_int($record) || is_null($record) ? $record : "'" . str_replace("'", "''", $record) . "'";
                                $columns[] = $record ?? 'null';
                            }

                            $this->fwrite($fp, ($key || $i ? ',' : '') . '(' . implode(',', $columns) . ')', $method);
                            unset($columns);
                        }
                        unset($cols);
                    }

                    $this->fwrite($fp, ";\n\n", $method);
                }

                $this->fclose($fp, $method);

                setFlash('success', __('admin.backup.database_success_saved'));

                return redirect('admin/backups');
            }

            setInput($request->all());
            setFlash('danger', $validator->getErrors());
        }

        $tables = DB::select('SHOW TABLE STATUS');

        $bzopen = function_exists('bzopen');
        $gzopen = function_exists('gzopen');

        $levels = range(0, 9);

        return view('admin/backups/create', compact('tables', 'bzopen', 'gzopen', 'levels'));
    }

    /**
     * Удаляет сохраненный бэкап
     *
     * @param Request   $request
     * @param Validator $validator
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Validator $validator): RedirectResponse
    {
        $file = $request->input('file');

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($file, __('admin.backup.backup_not_indicated'))
            ->regex($file, '|^[\w\.\-]+$|i', __('admin.backup.invalid_backup_name'))
            ->true(file_exists(storage_path('backups/' . $file)), __('admin.backup.backup_not_exist'));

        if ($validator->isValid()) {
            unlink(storage_path('backups/' . $file));

            setFlash('success', __('admin.backup.backup_success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('admin/backups');
    }

    /**
     * Открывает поток
     *
     * @param string $name
     * @param string $mode
     * @param string $method
     * @param int    $level
     *
     * @return bool|resource
     */
    private function fopen(string $name, string $mode, string $method, int $level)
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
     *
     * @param resource $fp
     * @param string   $str
     * @param string   $method
     */
    private function fwrite($fp, string $str, string $method): void
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
     *
     * @param resource $fp
     * @param string   $method
     */
    private function fclose($fp, string $method): void
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

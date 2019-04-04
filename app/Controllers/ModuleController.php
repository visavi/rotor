<?php

declare(strict_types=1);

namespace App\Controllers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

class ModuleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        [, $module] = explode('\\', static::class);

        View::addNamespace($module, MODULES . '/' . $module . '/resources/views');
        Lang::addNamespace($module, MODULES . '/' . $module . '/resources/lang');
    }
}

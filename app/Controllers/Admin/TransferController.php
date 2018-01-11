<?php

namespace App\Controllers\Admin;

use App\Classes\Request;
use App\Classes\Validator;
use App\Models\Guest;
use App\Models\Transfer;
use App\Models\User;

class TransferController extends AdminController
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Главная страница
     */
    public function index()
    {
        $total = Transfer::query()->count();
        $page = paginate(setting('listtransfers'), $total);

        $transfers = Transfer::query()
            ->orderBy('created_at', 'desc')
            ->limit($page['limit'])
            ->offset($page['offset'])
            ->with('user', 'recipientUser')
            ->get();

        return view('admin/transfer/index', compact('transfers', 'page'));
    }
}

<?php

namespace App\Controllers;

use App\Classes\Request;
use App\Models\Offer;

class OfferController extends BaseController
{
    /**
     * Главная страница
     */
    public function index($type = 'offer')
    {
        $otherType = $type == 'offer' ? 'issue' : 'offer';

        $sort = check(Request::input('sort', 'votes'));

        $total = Offer::query()->where('type', $type)->count();
        $page = paginate(setting('postoffers'), $total);

        $page['otherTotal'] = Offer::query()->where('type', $otherType)->count();

        switch ($sort) {
            case 'times':
                $order = 'created_at';
                break;
            case 'status':
                $order = 'status';
                break;
            case 'comments':
                $order = 'comments';
                break;
            default:
                $order = 'votes';
        }

        $offers = Offer::query()
            ->where('type', $type)
            ->orderBy($order, 'desc')
            ->offset($page['offset'])
            ->limit($page['limit'])
            ->with('user')
            ->get();

        return view('offer/index', compact('offers', 'page', 'sort', 'type'));
    }

    /**
     * Просмотр записи
     */
    public function view($id)
    {
        $offer = Offer::query()->find($id);

        if (! $offer) {
            abort(404, 'Данного предложения или проблемы не существует!');
        }

        return view('offer/view', compact('offer'));
    }
}

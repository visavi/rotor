<?php

declare(strict_types=1);

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    /**
     * Main page
     *
     * @return string
     */
    public function index(): string
    {
        return view('users/search');
    }

    /**
     * User search
     *
     * @param Request $request
     *
     * @return string
     */
    public function search(Request $request): string
    {
        $find   = $request->input('find');
        $strlen = utfStrlen($find);

        if ($strlen < 2 || $strlen > 20) {
            setInput($request->all());
            setFlash('danger', ['find' => __('users.request_requirements')]);
            redirect('/searchusers');
        }

        $users = User::query()
            ->where('login', 'like', '%' . $find . '%')
            ->orWhere('name', 'like', '%' . $find . '%')
            ->orderByDesc('point')
            ->paginate(setting('usersearch'));

        return view('users/search_result', compact('users'));
    }

    /**
     * First letter search
     *
     * @param string $letter
     *
     * @return string
     */
    public function sort(string $letter): string
    {
        $search = is_numeric($letter) ? "RLIKE '^[-0-9]'" : "LIKE '$letter%'";

        $users = User::query()
            ->whereRaw('login ' . $search)
            ->orderByDesc('point')
            ->paginate(setting('usersearch'));

        return view('users/search_result', compact('users'));
    }
}

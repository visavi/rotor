<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Main page
     */
    public function index(): View
    {
        return view('users/search');
    }

    /**
     * User search
     */
    public function search(Request $request): View|RedirectResponse
    {
        $find = $request->input('find');
        $strlen = utfStrlen($find);

        if ($strlen < 2 || $strlen > 20) {
            setInput($request->all());
            setFlash('danger', ['find' => __('users.request_requirements')]);

            return redirect('searchusers');
        }

        $users = User::query()
            ->where('login', 'like', '%' . $find . '%')
            ->orWhere('name', 'like', '%' . $find . '%')
            ->orderByDesc('point')
            ->paginate(setting('usersearch'))
            ->appends(compact('find'));

        return view('users/search_result', compact('users'));
    }

    /**
     * First letter search
     */
    public function sort(string $letter): View
    {
        $search = is_numeric($letter) ? "RLIKE '^[-0-9]'" : "LIKE '$letter%'";

        $users = User::query()
            ->whereRaw('login ' . $search)
            ->orderByDesc('point')
            ->paginate(setting('usersearch'));

        return view('users/search_result', compact('users'));
    }
}

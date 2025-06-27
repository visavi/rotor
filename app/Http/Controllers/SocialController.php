<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Classes\Validator;
use App\Models\Social;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialController extends Controller
{
    public ?User $user;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $this->middleware('check.user');

        $this->middleware(function ($request, $next) {
            $this->user = $request->user();

            return $next($request);
        });
    }

    /**
     * Главная страница
     */
    public function index(): View|RedirectResponse
    {
        $socials = Social::query()
            ->where('user_id', $this->user->id)
            ->get();

        return view('socials/index', compact('socials'));
    }

    /**
     * Удаление привязки
     */
    public function delete(int $id, Request $request, Validator $validator): RedirectResponse
    {
        $social = Social::query()->where('user_id', $this->user->id)->find($id);

        $validator->equal($request->input('_token'), csrf_token(), __('validator.token'))
            ->notEmpty($social, __('socials.not_found_binding'));

        if ($validator->isValid()) {
            $social->delete();

            setFlash('success', __('socials.success_deleted'));
        } else {
            setFlash('danger', $validator->getErrors());
        }

        return redirect('socials');
    }
}

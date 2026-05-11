<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditorController extends AdminController
{
    private string $cssPath;
    private string $jsPath;

    public function __construct()
    {
        $this->cssPath = public_path('assets/custom.css');
        $this->jsPath = public_path('assets/custom.js');
    }

    public function index(): View
    {
        $css = file_exists($this->cssPath) ? file_get_contents($this->cssPath) : '';
        $js = file_exists($this->jsPath) ? file_get_contents($this->jsPath) : '';

        return view('admin/editor/index', compact('css', 'js'));
    }

    public function save(Request $request): RedirectResponse
    {
        $css = $request->input('css', '');
        $js = $request->input('js', '');

        if (! is_writable(dirname($this->cssPath))) {
            return redirect()
                ->route('admin.editor.index')
                ->with('flash.danger', __('admin.editor.not_writable'));
        }

        file_put_contents($this->cssPath, $css);
        file_put_contents($this->jsPath, $js);

        return redirect()
            ->route('admin.editor.index')
            ->with('flash.success', __('admin.editor.saved'));
    }
}

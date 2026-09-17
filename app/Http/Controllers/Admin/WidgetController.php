<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\DashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WidgetController extends AdminController
{
    /**
     * Настройка виджетов панели
     */
    public function index(DashboardService $dashboard): View
    {
        $widgets = $dashboard->declarations();
        $settings = DashboardService::settings();

        return view('admin/widgets/index', compact('widgets', 'settings'));
    }

    /**
     * Сохраняет состав и порядок виджетов
     */
    public function update(Request $request, DashboardService $dashboard): RedirectResponse
    {
        $keys = array_keys($dashboard->declarations());
        $enabled = (array) $request->input('widgets', []);

        // Порядок приходит строкой от Sortable; чужие ключи отсекаются, забытые уходят в конец
        $order = array_intersect(explode(',', (string) $request->input('order')), $keys);

        $keys = [...$order, ...array_diff($keys, $order)];

        // Выключенные тоже попадают в настройку, с минусом: иначе на следующем
        // заходе они выглядели бы как виджеты только что поставленного модуля
        $value = array_map(
            static fn ($key) => in_array($key, $enabled, true) ? $key : '-' . $key,
            $keys,
        );

        Setting::query()
            ->where('name', DashboardService::SETTING)
            ->update(['value' => implode(',', $value)]);

        clearCache('settings');

        return redirect()
            ->route('admin.widgets.index')
            ->with('success', __('index.widgets_saved'));
    }
}

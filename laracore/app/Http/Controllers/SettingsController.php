<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\GlobalSetting;
use App\Models\TenantSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Контроллер локальных настроек площадки.
 * Позволяет переопределять глобальные параметры.
 */
class SettingsController extends Controller
{
    /**
     * Отображение списка настроек.
     */
    public function index(): View
    {
        // 1. Получаем все глобальные настройки (сгруппированные по модулям)
        $globalSettings = GlobalSetting::all()->groupBy('module');

        // 2. Получаем все локальные переопределения
        $tenantSettings = TenantSetting::all()->keyBy(function ($item) {
            return "{$item->module}.{$item->key}";
        });

        return view('settings.index', compact('globalSettings', 'tenantSettings'));
    }

    /**
     * Массовое обновление (переопределение) настроек.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.module' => ['required', 'string'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
        ]);

        foreach ($validated['settings'] as $item) {
            // Проверяем, разрешено ли переопределение этой настройки
            $global = GlobalSetting::where('module', $item['module'])
                ->where('key', $item['key'])
                ->first();

            if (!$global || !$global->is_overridable) {
                continue; // Пропускаем системные настройки
            }

            // Сохраняем или обновляем локальное значение
            TenantSetting::updateOrCreate(
                [
                    'module' => $item['module'],
                    'key' => $item['key'],
                ],
                [
                    'value' => $item['value'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}

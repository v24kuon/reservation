<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function edit(): View
    {
        $availableByTable = NotificationTemplate::getAvailableVariablesByTable();
        $tableLabels = NotificationTemplate::getTableLabels();
        $selected = SystemSetting::getJson('email_variables_whitelist', []);

        return view('admin.settings.edit', compact('availableByTable', 'tableLabels', 'selected'));
    }

    public function update(Request $request): RedirectResponse
    {
        // 許可されるプレースホルダ一覧を算出
        $groups = NotificationTemplate::getAvailableVariablesByTable();
        $allowed = [];
        foreach ($groups as $m) {
            $allowed = array_merge($allowed, array_keys($m));
        }
        $allowed = array_values(array_unique($allowed));

        $request->validate([
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string', 'distinct', Rule::in($allowed)],
        ]);

        $variables = array_values(array_unique($request->input('variables', [])));
        SystemSetting::put('email_variables_whitelist', array_values($variables), 'json', 'メールで使用可能な変数の許可リスト');

        return redirect()->route('admin.settings.edit')->with('status', '設定を更新しました');
    }
}

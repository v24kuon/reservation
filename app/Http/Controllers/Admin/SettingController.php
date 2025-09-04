<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\SystemSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        $request->validate([
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
        ]);

        $variables = $request->input('variables', []);
        SystemSetting::put('email_variables_whitelist', array_values($variables), 'json', 'メールで使用可能な変数の許可リスト');

        return redirect()->route('admin.settings.edit')->with('status', '設定を更新しました');
    }
}

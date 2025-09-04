<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationTemplateRequest;
use App\Http\Requests\UpdateNotificationTemplateRequest;
use App\Models\NotificationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $templates = NotificationTemplate::query()
            ->latest('id')
            ->paginate(15);

        return view('admin.notification_templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $availableByTable = NotificationTemplate::getAvailableVariablesByTable();
        $tableLabels = NotificationTemplate::getTableLabels();

        return view('admin.notification_templates.create', compact('availableByTable', 'tableLabels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNotificationTemplateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['variables'] = isset($data['variables']) ? json_decode($data['variables'], true) : null;
        NotificationTemplate::create($data);

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('status', 'テンプレートを作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(NotificationTemplate $notification_template): View
    {
        return view('admin.notification_templates.show', ['template' => $notification_template]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NotificationTemplate $notification_template): View
    {
        $availableByTable = NotificationTemplate::getAvailableVariablesByTable();
        $tableLabels = NotificationTemplate::getTableLabels();

        return view('admin.notification_templates.edit', [
            'template' => $notification_template,
            'availableByTable' => $availableByTable,
            'tableLabels' => $tableLabels,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNotificationTemplateRequest $request, NotificationTemplate $notification_template): RedirectResponse
    {
        $data = $request->validated();
        $data['variables'] = isset($data['variables']) ? json_decode($data['variables'], true) : $notification_template->variables;
        $notification_template->update($data);

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('status', 'テンプレートを更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationTemplate $notification_template): RedirectResponse
    {
        $notification_template->delete();

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('status', 'テンプレートを削除しました');
    }
}

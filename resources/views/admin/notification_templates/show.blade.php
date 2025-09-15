<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート詳細</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-2">
        <p><strong>ID:</strong> {{ $template->id }}</p>
        <p><strong>名称:</strong> {{ $template->name }}</p>
        <p><strong>種別:</strong> {{ $template->type }}</p>
        <p><strong>件名:</strong> {{ $template->subject }}</p>
        <p><strong>有効:</strong> {{ $template->is_active ? 'はい' : 'いいえ' }}</p>
        <div>
            <p class="font-semibold">本文（テキスト）</p>
            <pre class="border rounded p-2 whitespace-pre-wrap">{{ $template->body_text }}</pre>
        </div>

        <div>
            <p class="font-semibold">変数</p>
            <pre class="border rounded p-2 whitespace-pre-wrap">@json($template->variables ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
        </div>

                    <div class="pt-4 flex space-x-2">
                        <x-primary-button as="a" href="{{ route('admin.notification-templates.edit', $template) }}">編集</x-primary-button>
                        <x-secondary-button href="{{ route('admin.notification-templates.index') }}">戻る</x-secondary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

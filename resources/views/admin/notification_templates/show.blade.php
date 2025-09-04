<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">通知テンプレート詳細</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6 space-y-2">
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
            <pre class="border rounded p-2 whitespace-pre-wrap">{{ json_encode($template->variables) }}</pre>
        </div>

        <div class="pt-4">
            <a href="{{ route('admin.notification-templates.edit', $template) }}" class="bg-blue-600 text-white px-4 py-2 rounded">編集</a>
            <a href="{{ route('admin.notification-templates.index') }}" class="px-4 py-2 rounded border">一覧へ戻る</a>
        </div>
    </div>
</x-app-layout>



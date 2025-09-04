<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">システム設定</h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <div class="border rounded p-3 bg-gray-50">
                <div class="font-semibold mb-2">メールで使用可能な変数（許可リスト）</div>
                <div class="grid md:grid-cols-2 gap-4">
                    @php($currentSelected = old('variables', $selected ?? []))
                    @foreach($availableByTable as $table => $placeholders)
                        <fieldset class="border rounded p-2">
                            <legend class="text-sm font-medium px-1">{{ $tableLabels[$table] ?? $table }}</legend>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2">
                                @foreach($placeholders as $placeholder => $column)
                                    <label class="inline-flex items-center text-sm">
                                        <input type="checkbox" name="variables[]" value="{{ $placeholder }}" @checked(in_array($placeholder, $currentSelected, true))>
                                        <span class="ml-2">&#123;&#123;{{ $placeholder }}&#125;&#125; <span class="text-gray-500">({{ $column }})</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">保存</button>
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded border">ダッシュボードへ戻る</a>
            </div>
        </form>
    </div>
</x-app-layout>



@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="氏名" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $instructor->name ?? '')" required autofocus autocomplete="name" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="メールアドレス" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $instructor->email ?? '')" required autocomplete="email" />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="password" value="パスワード" />
        <x-text-input
            id="password"
            name="password"
            type="password"
            class="mt-1 block w-full"
            :required="!isset($instructor)"
            minlength="8"
            autocomplete="new-password"
            :placeholder="isset($instructor) ? '変更する場合のみ入力' : null" />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="パスワード（確認）" />
        <x-text-input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            class="mt-1 block w-full"
            :required="!isset($instructor)"
            autocomplete="new-password"
            :placeholder="isset($instructor) ? '確認のため再入力' : null" />
        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
    </div>

    {{-- role はサーバ側で固定代入する --}}

    @if(($showActions ?? true))
        <div class="flex items-center gap-3">
            <x-primary-button>保存する</x-primary-button>
            <a href="{{ route('admin.instructors.index') }}" class="px-4 py-2 bg-gray-200 rounded">戻る</a>
        </div>
    @endif
</div>

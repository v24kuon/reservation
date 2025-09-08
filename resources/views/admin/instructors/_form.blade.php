@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="氏名" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $instructor->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="email" value="メールアドレス" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $instructor->email ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('email')" />
    </div>

    <div>
        <x-input-label for="password" value="パスワード" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :value="old('password')" required />
        <x-input-error class="mt-2" :messages="$errors->get('password')" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="パスワード（確認）" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :value="old('password_confirmation')" required />
        <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
    </div>

    <input type="hidden" name="role" value="instructor" />

    <div class="flex items-center gap-3">
        <x-primary-button>保存する</x-primary-button>
        <a href="{{ route('admin.instructors.index') }}" class="text-gray-600">戻る</a>
    </div>
</div>

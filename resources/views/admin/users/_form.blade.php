@php
    $method = $method ?? 'POST';
    $user = $user ?? null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium">名前</label>
            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" required maxlength="255" value="{{ old('name', $user->name ?? '') }}" aria-invalid="@error('name') true @else false @enderror" autocomplete="name" @if(!$user) autofocus @endif>
            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium">メール</label>
            <input id="email" name="email" type="email" class="mt-1 w-full border rounded p-2" required maxlength="255" value="{{ old('email', $user->email ?? '') }}" aria-invalid="@error('email') true @else false @enderror" autocomplete="email">
            @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <!-- role input removed: creates only general users -->

        <div>
            <label for="password" class="block text-sm font-medium">パスワード @if($user) <span class="text-xs text-gray-500">（更新時のみ）</span> @endif</label>
            <input id="password" name="password" type="password" class="mt-1 w-full border rounded p-2" @if(!$user) required @endif minlength="8" aria-invalid="@error('password') true @else false @enderror" autocomplete="new-password">
            @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="pt-4 flex space-x-2">
            <x-primary-button type="submit">保存</x-primary-button>
            <x-secondary-button href="{{ route('admin.users.index') }}">戻る</x-secondary-button>
        </div>
    </div>
</form>

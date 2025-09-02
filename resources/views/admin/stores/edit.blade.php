<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            店舗編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.stores.update', $store) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium">名称</label>
                            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" value="{{ old('name', $store->name) }}" required autocomplete="organization">
                            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium">住所</label>
                            <textarea id="address" name="address" class="mt-1 w-full border rounded p-2" rows="2" required autocomplete="street-address">{{ old('address', $store->address) }}</textarea>
                            @error('address')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium">電話番号</label>
                            <input id="phone" name="phone" type="text" class="mt-1 w-full border rounded p-2" value="{{ old('phone', $store->phone) }}" required inputmode="tel" autocomplete="tel" pattern="^\+?[0-9()\-\s]{7,20}$" minlength="7" maxlength="20">
                            @error('phone')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="access_info" class="block text-sm font-medium">アクセス情報</label>
                            <textarea id="access_info" name="access_info" class="mt-1 w-full border rounded p-2" rows="2">{{ old('access_info', $store->access_info) }}</textarea>
                            @error('access_info')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="google_map_url" class="block text-sm font-medium">GoogleマップURL</label>
                            <input id="google_map_url" name="google_map_url" type="url" class="mt-1 w-full border rounded p-2" value="{{ old('google_map_url', $store->google_map_url) }}" autocomplete="url">
                            @error('google_map_url')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="parking_info" class="block text-sm font-medium">駐車場情報</label>
                            <textarea id="parking_info" name="parking_info" class="mt-1 w-full border rounded p-2" rows="2">{{ old('parking_info', $store->parking_info) }}</textarea>
                            @error('parking_info')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium">備考</label>
                            <textarea id="notes" name="notes" class="mt-1 w-full border rounded p-2" rows="3">{{ old('notes', $store->notes) }}</textarea>
                            @error('notes')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="flex items-center space-x-2">
                            <input type="hidden" name="is_active" value="0">
                            <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded" @checked(old('is_active', $store->is_active))>
                            <label for="is_active">有効</label>
                        </div>

                        <div class="pt-4 flex space-x-2">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded" type="submit">更新</button>
                            <a href="{{ route('admin.stores.index') }}" class="px-4 py-2 bg-gray-200 rounded">戻る</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

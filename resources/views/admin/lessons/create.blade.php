<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスン作成
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.lessons.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label for="name" class="block text-sm font-medium">名称</label>
                            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" value="{{ old('name') }}" required>
                            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="store_id" class="block text-sm font-medium">店舗</label>
                                <select id="store_id" name="store_id" class="mt-1 w-full border rounded p-2" required>
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                @error('store_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="category_id" class="block text-sm font-medium">カテゴリ（子）</label>
                                <select id="category_id" name="category_id" class="mt-1 w-full border rounded p-2" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="instructor_user_id" class="block text-sm font-medium">インストラクター</label>
                                <select id="instructor_user_id" name="instructor_user_id" class="mt-1 w-full border rounded p-2" required>
                                    @foreach($instructors as $inst)
                                        <option value="{{ $inst->id }}" @selected(old('instructor_user_id') == $inst->id)>{{ $inst->name }}</option>
                                    @endforeach
                                </select>
                                @error('instructor_user_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="duration" class="block text-sm font-medium">時間（分）</label>
                                <input id="duration" name="duration" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('duration', 60) }}" min="10" max="600" required>
                                @error('duration')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="capacity" class="block text-sm font-medium">定員</label>
                                <input id="capacity" name="capacity" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('capacity', 10) }}" min="1" max="500" required>
                                @error('capacity')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="booking_deadline_hours" class="block text-sm font-medium">予約期限（時間）</label>
                                <input id="booking_deadline_hours" name="booking_deadline_hours" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('booking_deadline_hours', 24) }}" min="0" max="336" required>
                                @error('booking_deadline_hours')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="cancel_deadline_hours" class="block text-sm font-medium">キャンセル期限（時間）</label>
                                <input id="cancel_deadline_hours" name="cancel_deadline_hours" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('cancel_deadline_hours', 24) }}" min="0" max="336" required>
                                @error('cancel_deadline_hours')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center space-x-2 mt-6">
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded" @checked(old('is_active', true))>
                                <label for="is_active">有効</label>
                            </div>
                        </div>

                        <div class="pt-4 flex space-x-2">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded" type="submit">作成</button>
                            <a href="{{ route('admin.lessons.index') }}" class="px-4 py-2 bg-gray-200 rounded">戻る</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>



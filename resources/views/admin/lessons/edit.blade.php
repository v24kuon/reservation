<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            レッスン編集
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="name" class="block text-sm font-medium">名称</label>
                            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" value="{{ old('name', $lesson->name) }}" required aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="name-error">
                            @error('name')<p id="name-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="store_id" class="block text-sm font-medium">店舗</label>
                                <select id="store_id" name="store_id" class="mt-1 w-full border rounded p-2" required aria-invalid="{{ $errors->has('store_id') ? 'true' : 'false' }}" aria-describedby="store_id-error">
                                    @foreach($stores as $store)
                                        <option value="{{ $store->id }}" @selected(old('store_id', $lesson->store_id) == $store->id)>{{ $store->name }}</option>
                                    @endforeach
                                </select>
                                @error('store_id')<p id="store_id-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="category_id" class="block text-sm font-medium">カテゴリ（子）</label>
                                <select id="category_id" name="category_id" class="mt-1 w-full border rounded p-2" required aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}" aria-describedby="category_id-error">
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id', $lesson->category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<p id="category_id-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="instructor_user_id" class="block text-sm font-medium">インストラクター</label>
                                <select id="instructor_user_id" name="instructor_user_id" class="mt-1 w-full border rounded p-2" required aria-invalid="{{ $errors->has('instructor_user_id') ? 'true' : 'false' }}" aria-describedby="instructor_user_id-error">
                                    @foreach($instructors as $inst)
                                        <option value="{{ $inst->id }}" @selected(old('instructor_user_id', $lesson->instructor_user_id) == $inst->id)>{{ $inst->name }}</option>
                                    @endforeach
                                </select>
                                @error('instructor_user_id')<p id="instructor_user_id-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="duration" class="block text-sm font-medium">時間（分）</label>
                                <input id="duration" name="duration" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('duration', $lesson->duration) }}" min="10" max="600" step="1" required aria-invalid="{{ $errors->has('duration') ? 'true' : 'false' }}" aria-describedby="duration-error">
                                @error('duration')<p id="duration-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="capacity" class="block text-sm font-medium">定員</label>
                                <input id="capacity" name="capacity" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('capacity', $lesson->capacity) }}" min="1" max="500" step="1" required aria-invalid="{{ $errors->has('capacity') ? 'true' : 'false' }}" aria-describedby="capacity-error">
                                @error('capacity')<p id="capacity-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="booking_deadline_hours" class="block text-sm font-medium">予約期限（時間）</label>
                                <input id="booking_deadline_hours" name="booking_deadline_hours" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('booking_deadline_hours', $lesson->booking_deadline_hours) }}" min="0" max="336" step="1" required aria-invalid="{{ $errors->has('booking_deadline_hours') ? 'true' : 'false' }}" aria-describedby="booking_deadline_hours-error">
                                @error('booking_deadline_hours')<p id="booking_deadline_hours-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="cancel_deadline_hours" class="block text-sm font-medium">キャンセル期限（時間）</label>
                                <input id="cancel_deadline_hours" name="cancel_deadline_hours" type="number" class="mt-1 w-full border rounded p-2" value="{{ old('cancel_deadline_hours', $lesson->cancel_deadline_hours) }}" min="0" max="336" step="1" required aria-invalid="{{ $errors->has('cancel_deadline_hours') ? 'true' : 'false' }}" aria-describedby="cancel_deadline_hours-error">
                                @error('cancel_deadline_hours')<p id="cancel_deadline_hours-error" class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center space-x-2 mt-6">
                                <input type="hidden" name="is_active" value="0">
                                <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded" @checked(old('is_active', $lesson->is_active))>
                                <label for="is_active">有効</label>
                            </div>
                        </div>

                        <div class="pt-4 flex space-x-2">
                            <x-primary-button type="submit">更新</x-primary-button>
                            <x-secondary-button href="{{ route('admin.lessons.index') }}">戻る</x-secondary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

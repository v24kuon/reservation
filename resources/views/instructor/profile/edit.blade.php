<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            プロフィール編集（講師）
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('instructor.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="image" value="プロフィール画像 (jpg/png/webp, 最大10MB)" />
                                <input id="image" name="image" type="file" class="mt-1 block w-full" accept="image/jpeg,image/png,image/webp" />
                                @if($profile->image_url)
                                    <div class="mt-2">
                                        <img src="{{ $profile->image_url }}" alt="現在の画像" class="h-24 w-24 object-cover rounded" />
                                    </div>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" name="remove_image" value="1">
                                            <span>画像を削除する</span>
                                        </label>
                                    </div>
                                @endif
                                <x-input-error class="mt-2" :messages="$errors->get('image')" />
                            </div>

                            <div>
                                <x-input-label for="bio" value="自己紹介" />
                                <textarea id="bio" name="bio" rows="5" class="mt-1 block w-full">{{ old('bio', $profile->bio) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                            </div>

                            <div>
                                <x-input-label for="qualifications" value="資格（改行区切り）" />
                                <textarea id="qualifications" name="qualifications" rows="5" class="mt-1 block w-full">{{ old('qualifications', $profile->qualifications) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('qualifications')" />
                            </div>

                            <div>
                                <x-input-label for="notes" value="備考" />
                                <textarea id="notes" name="notes" rows="4" class="mt-1 block w-full">{{ old('notes', $profile->notes) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button>保存する</x-primary-button>
                                <a href="{{ route('dashboard') }}" class="text-gray-600">戻る</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

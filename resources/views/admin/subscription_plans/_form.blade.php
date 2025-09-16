@php
    $method = $method ?? 'POST';
    $plan = $plan ?? null;
    $categories = $categories ?? collect();
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium">プラン名</label>
            <input id="name" name="name" type="text" class="mt-1 w-full border rounded p-2" required maxlength="255" value="{{ old('name', $plan->name ?? '') }}" aria-invalid="@error('name') true @else false @enderror">
            @error('name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="price" class="block text-sm font-medium">価格（円）</label>
                <input id="price" name="price" type="number" min="1" class="mt-1 w-full border rounded p-2 bg-gray-100" readonly value="{{ old('price', $plan->price ?? '') }}" aria-invalid="@error('price') true @else false @enderror" placeholder="Stripe Price から自動取得">
                @error('price')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                <p id="price-help" class="text-xs text-gray-500 mt-1">StripeのPrice IDを入力すると自動で反映されます</p>
            </div>
            <div>
                <label for="lesson_count" class="block text-sm font-medium">月間レッスン回数</label>
                <input id="lesson_count" name="lesson_count" type="number" min="1" class="mt-1 w-full border rounded p-2" required value="{{ old('lesson_count', $plan->lesson_count ?? '') }}" aria-invalid="@error('lesson_count') true @else false @enderror">
                @error('lesson_count')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">対象カテゴリ</label>
            @php
                $selectedIds = collect(old('allowed_category_ids', $plan->allowed_category_ids ?? []))->map(fn($v) => (int) $v)->all();
                $roots = $categories->whereNull('parent_id');
                $childrenByParent = $categories->whereNotNull('parent_id')->groupBy('parent_id');
            @endphp
            <div class="space-y-3" id="category-tree">
                @foreach($roots as $root)
                    @php $children = $childrenByParent->get($root->id, collect()); @endphp
                    <div class="border rounded p-3">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" class="parent-category" data-parent-id="{{ $root->id }}" id="parent-{{ $root->id }}">
                            <span class="font-medium">{{ $root->name }}</span>
                        </label>
                        @if($children->isNotEmpty())
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 pl-6">
                                @foreach($children as $child)
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" name="allowed_category_ids[]" class="child-category" data-parent-id="{{ $root->id }}" value="{{ $child->id }}" @checked(in_array($child->id, $selectedIds, true))>
                                        <span>{{ $child->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @error('allowed_category_ids')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="stripe_product_id" class="block text-sm font-medium">Stripe Product ID</label>
                <input id="stripe_product_id" name="stripe_product_id" type="text" class="mt-1 w-full border rounded p-2" required value="{{ old('stripe_product_id', $plan->stripe_product_id ?? '') }}" placeholder="prod_..." aria-invalid="@error('stripe_product_id') true @else false @enderror">
                @error('stripe_product_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="stripe_price_id" class="block text-sm font-medium">Stripe Price ID</label>
                <input id="stripe_price_id" name="stripe_price_id" type="text" class="mt-1 w-full border rounded p-2" required value="{{ old('stripe_price_id', $plan->stripe_price_id ?? '') }}" placeholder="price_..." aria-invalid="@error('stripe_price_id') true @else false @enderror" aria-describedby="price-help">
                @error('stripe_price_id')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium">説明（任意）</label>
            <textarea id="description" name="description" rows="3" class="mt-1 w-full border rounded p-2" aria-invalid="@error('description') true @else false @enderror">{{ old('description', $plan->description ?? '') }}</textarea>
            @error('description')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center space-x-2">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" class="rounded" @checked(old('is_active', $plan->is_active ?? true))>
            <label for="is_active">有効</label>
        </div>

        <div class="pt-4 flex space-x-2">
            <x-primary-button type="submit">保存</x-primary-button>
            <x-secondary-button href="{{ route('admin.subscription-plans.index') }}">戻る</x-secondary-button>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function() {
    const priceInput = document.getElementById('price');
    const priceIdInput = document.getElementById('stripe_price_id');
    let timer;

    function debounce(fn, wait) {
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    async function lookupPrice(priceId) {
        if (!priceId || !/^price_[A-Za-z0-9]+$/.test(priceId)) return;
        try {
            const res = await fetch('{{ route('admin.subscription-plans.price-lookup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ price_id: priceId })
            });
            const data = await res.json();
            if (data && data.success) {
                priceInput.value = data.price;
            }
        } catch (e) {
            // no-op
        }
    }

    priceIdInput?.addEventListener('input', debounce((e) => {
        lookupPrice(e.target.value.trim());
    }, 400));

    // 初期値がある場合も取得
    if (priceIdInput?.value) {
        lookupPrice(priceIdInput.value.trim());
    }

    // Category tree interactions
    const parentCheckboxes = document.querySelectorAll('#category-tree .parent-category');
    const childCheckboxes = document.querySelectorAll('#category-tree .child-category');

    function syncParentState(parentId) {
        const parent = document.querySelector(`#category-tree .parent-category[data-parent-id="${parentId}"]`);
        if (!parent) return;
        const children = document.querySelectorAll(`#category-tree .child-category[data-parent-id="${parentId}"]`);
        const total = children.length;
        const checked = Array.from(children).filter(c => c.checked).length;
        if (checked === 0) {
            parent.checked = false;
            parent.indeterminate = false;
        } else if (checked === total) {
            parent.checked = true;
            parent.indeterminate = false;
        } else {
            parent.checked = false;
            parent.indeterminate = true;
        }
    }

    parentCheckboxes.forEach(parent => {
        parent.addEventListener('change', () => {
            const pid = parent.getAttribute('data-parent-id');
            const children = document.querySelectorAll(`#category-tree .child-category[data-parent-id="${pid}"]`);
            children.forEach(c => { c.checked = parent.checked; });
            syncParentState(pid);
        });
        // 初期状態同期
        syncParentState(parent.getAttribute('data-parent-id'));
    });

    childCheckboxes.forEach(child => {
        child.addEventListener('change', () => {
            const pid = child.getAttribute('data-parent-id');
            syncParentState(pid);
        });
    });
})();
</script>
@endpush

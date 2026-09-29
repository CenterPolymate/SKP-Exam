@props(['label', 'model', 'options' => [], 'required' => false, 'live' => false])

<div>
    <label class="block min-h-10 text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <div class="mt-2 flex flex-wrap gap-x-6 gap-y-2">
        @foreach ($options as $value => $optionLabel)
            <label class="inline-flex items-center gap-2 text-sm text-stone-600">
                <input
                    type="radio"
                    wire:model{{ $live ? '.live' : '' }}="{{ $model }}"
                    value="{{ $value }}"
                    class="border-stone-300 text-amber-600 focus:ring-amber-500"
                >
                {{ $optionLabel }}
            </label>
        @endforeach
    </div>
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

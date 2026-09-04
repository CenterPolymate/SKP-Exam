@props(['label', 'model', 'required' => false, 'placeholder' => null, 'rows' => 3])

<div>
    <label class="block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <textarea
        wire:model="{{ $model }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-stone-300 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-500']) }}
    ></textarea>
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

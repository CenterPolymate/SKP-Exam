@props(['label', 'model', 'type' => 'text', 'required' => false, 'placeholder' => null, 'live' => false, 'disabled' => false, 'wrapperClass' => ''])

<div class="{{ $wrapperClass }}">
    <label class="block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <input
        type="{{ $type }}"
        @if ($disabled)
            disabled
        @else
            wire:model{{ $live ? '.live' : '' }}="{{ $model }}"
        @endif
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-stone-300 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-500 disabled:bg-stone-100 disabled:text-stone-400']) }}
    >
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

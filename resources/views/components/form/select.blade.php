@props(['label', 'model', 'options' => [], 'required' => false, 'placeholder' => '--เลือก--', 'live' => false])

<div>
    <label class="block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <select
        wire:model{{ $live ? '.live' : '' }}="{{ $model }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-stone-300 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-500']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ $optionLabel }}</option>
        @endforeach
    </select>
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

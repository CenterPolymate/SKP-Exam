@props(['label', 'model', 'required' => false, 'accept' => null, 'hint' => null, 'locale' => 'th'])

<div>
    <label class="block text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    @if ($hint)
        <p class="text-xs text-stone-400">{{ $hint }}</p>
    @endif
    <input
        type="file"
        wire:model="{{ $model }}"
        accept="{{ $accept }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-amber-700 hover:file:bg-amber-100']) }}
    >
    <div wire:loading wire:target="{{ $model }}" class="mt-1 text-xs text-amber-600">{{ $locale === 'en' ? 'Uploading...' : 'กำลังอัพโหลด...' }}</div>
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

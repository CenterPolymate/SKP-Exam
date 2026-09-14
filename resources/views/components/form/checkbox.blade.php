@props(['model', 'required' => false])

<div>
    <label class="flex items-start gap-3 text-sm text-stone-600">
        <input
            type="checkbox"
            wire:model="{{ $model }}"
            value="1"
            class="mt-1 shrink-0 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
        >
        <span>@if ($required)<span class="text-red-500">* </span>@endif{{ $slot }}</span>
    </label>
    @error($model)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

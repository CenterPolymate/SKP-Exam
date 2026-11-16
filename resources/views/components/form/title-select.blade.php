@props(['label', 'lang', 'choice', 'presets', 'required' => false, 'locale' => 'th'])

@php
    $isThai = $lang === 'th';
    $placeholder = $locale === 'en' ? '--Select--' : '--เลือก--';
    $otherLabel = $isThai ? 'อื่นๆ' : 'Other';
    $otherPlaceholder = $isThai
        ? ($locale === 'en' ? 'Specify title (e.g. ดร., ร้อยตรี)' : 'ระบุคำนำหน้า เช่น ดร., ร้อยตรี')
        : 'Specify title (e.g. Dr.)';
    $choiceModel = "title_{$lang}_choice";
    $otherModel = "title_{$lang}_other";
    $valueModel = "title_{$lang}";
@endphp

<div>
    <label class="block min-h-10 text-sm font-medium text-stone-700">
        {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
    </label>
    <select
        wire:model.live="{{ $choiceModel }}"
        {{ $attributes->merge(['class' => 'mt-1 block w-full rounded-lg border-stone-300 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-500']) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($presets as $preset)
            <option value="{{ $preset }}">{{ $preset }}</option>
        @endforeach
        <option value="other">{{ $otherLabel }}</option>
    </select>

    @if ($choice === 'other')
        <input
            type="text"
            wire:model.live.debounce.300ms="{{ $otherModel }}"
            maxlength="255"
            placeholder="{{ $otherPlaceholder }}"
            class="mt-2 block w-full rounded-lg border-stone-300 text-sm text-stone-700 focus:border-amber-500 focus:ring-amber-500"
        />
    @endif

    @error($valueModel)
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

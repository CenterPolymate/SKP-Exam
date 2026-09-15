@props(['label'])

<div>
    <dt class="text-xs text-stone-400">{{ $label }}</dt>
    <dd class="text-sm font-medium text-stone-700">{{ $slot->toHtml() !== '' ? $slot : '-' }}</dd>
</div>

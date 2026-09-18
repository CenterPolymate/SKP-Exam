@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-stone-300 text-stone-700 focus:border-amber-600 focus:ring-amber-600 rounded-lg shadow-sm']) }}>

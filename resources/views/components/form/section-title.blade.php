@props(['thai', 'english' => null])

<div class="mt-8 border-t border-stone-100 pt-6 first:mt-0 first:border-t-0 first:pt-0">
    <h3 class="text-sm font-semibold text-amber-700">
        {{ $thai }} @if ($english)<span class="font-normal text-stone-400">/ {{ $english }}</span>@endif
    </h3>
</div>

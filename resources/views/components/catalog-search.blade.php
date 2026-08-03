@props(['privateSearch' => false])

<dialog id="catalog-search-dialog" data-catalog-search-dialog data-search-url="{{ $privateSearch ? route('client.products') : route('products.search') }}" data-search-mode="{{ $privateSearch ? 'redirect' : 'suggest' }}" aria-labelledby="catalog-search-title" class="m-auto w-[min(680px,calc(100vw-32px))] overflow-hidden rounded-[24px] border border-white/70 bg-white p-0 text-[#132b3b] shadow-[0_30px_100px_rgba(7,19,27,.32)] backdrop:bg-[#132b3b]/45 backdrop:backdrop-blur-sm">
    <div class="border-b border-[#e8edef] px-5 pt-5 pb-4 sm:px-6 sm:pt-6">
        <div class="flex items-start gap-4">
            <span class="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#eaf2fb] text-[#1954b7]" aria-hidden="true">
                <svg class="size-5" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"/><path d="m16 16 4.25 4.25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
            <div class="min-w-0 grow"><p class="text-[10px] font-semibold tracking-[.14em] text-[#1954b7]">FITINOX · BÚSQUEDA INTELIGENTE</p><h2 id="catalog-search-title" class="mt-1 text-[21px] font-semibold leading-7">{{ $privateSearch ? 'Buscá en tu catálogo privado' : 'Encontrá la solución indicada' }}</h2></div>
            <button type="button" data-catalog-search-close class="grid size-11 shrink-0 place-items-center rounded-full text-[22px] leading-none text-[#65747c] transition hover:bg-[#f1f4f5] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20" aria-label="Cerrar buscador">×</button>
        </div>
        <form @if($privateSearch) action="{{ route('client.products') }}" method="GET" @endif data-catalog-search-form class="mt-5">
        <label class="relative block">
            <span class="sr-only">Buscar productos Fitinox</span>
            <svg class="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-[#52636c]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="2"/><path d="m16 16 4.25 4.25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input data-catalog-search-input @if($privateSearch) name="q" @endif type="search" autocomplete="off" placeholder="Ej.: recto macho, AL-FSS-200-1-2, rosca 1/4&quot;" class="h-14 w-full rounded-[14px] border border-[#cfd9df] bg-[#fbfcfd] pr-12 pl-12 text-[15px] text-[#132b3b] outline-none transition placeholder:text-[#849198] focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/10">
            <span data-catalog-search-spinner class="pointer-events-none absolute top-1/2 right-4 hidden size-5 -translate-y-1/2 animate-spin rounded-full border-2 border-[#dce6ec] border-t-[#1954b7]" aria-hidden="true"></span>
        </label>
        @if($privateSearch)
            <div class="mt-4 flex items-center justify-between gap-4"><p class="text-[12px] leading-5 text-[#65747c]">Buscá por producto o código. Te llevamos al listado privado ya filtrado.</p><button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center rounded-[7px] bg-[#132b3b] px-4 text-[12px] font-semibold text-white transition hover:bg-[#1954b7]">VER PRODUCTOS</button></div>
        @else
            <p class="mt-3 text-[12px] leading-5 text-[#65747c]">Podés buscar por producto, código, categoría, tubo, rosca, calidad o aplicación.</p>
        @endif
        </form>
    </div>
    <div class="max-h-[min(52vh,490px)] overflow-y-auto p-3 sm:p-4 {{ $privateSearch ? 'hidden' : '' }}">
        <div data-catalog-search-empty class="rounded-[16px] bg-[#f6f8f9] px-5 py-6 text-center"><p class="text-[14px] font-medium text-[#334851]">¿Qué estás buscando?</p><p class="mt-1 text-[12px] leading-5 text-[#65747c]">Escribí una necesidad o referencia y Fitinox te muestra las coincidencias más relevantes.</p></div>
        <div data-catalog-search-response class="hidden px-2 pt-1 pb-3 text-[12px] leading-5 text-[#52636c]" aria-live="polite"></div>
        <div data-catalog-search-results class="space-y-2" role="listbox" aria-label="Resultados de productos"></div>
    </div>
    <div class="flex items-center justify-between border-t border-[#e8edef] bg-[#fbfcfd] px-5 py-3 text-[11px] text-[#65747c] sm:px-6"><span>Asistente de catálogo Fitinox</span><span class="hidden sm:inline">⌘ / Ctrl + K para buscar</span></div>
</dialog>

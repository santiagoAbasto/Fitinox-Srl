@extends('layouts.client')

@section('title', 'Productos | Zona Clientes')

@section('content')
<section class="fitinox-content-1224 min-h-[620px] pt-[24px] pb-[96px]">
    <nav aria-label="Migas de pan" class="text-[14px] leading-[21px] text-[#35363a]">
        <button type="button" data-private-public-link class="font-semibold hover:underline">Inicio</button><span class="px-1">&gt;</span><span>Productos</span>
    </nav>

    @php($selectedCategory = $categories->firstWhere('id', (int) ($query['category'] ?? 0))?->name ?? 'Todas las categorías')

    <form method="GET" class="relative mt-[48px] flex flex-col gap-5 xl:h-[48px] xl:block">
        <label class="flex h-[48px] w-full items-center rounded-[6px] border border-[#dedfe0] bg-white px-[18px] sm:w-[287px]">
            <span class="sr-only">¿Qué estás buscando?</span>
            <button type="submit" aria-label="Buscar" class="grid size-5 shrink-0 place-items-center text-[#132b3b]">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M17.5 17.5L13.8833 13.8833M15.8333 9.16667C15.8333 12.8486 12.8486 15.8333 9.16667 15.8333C5.48477 15.8333 2.5 12.8486 2.5 9.16667C2.5 5.48477 5.48477 2.5 9.16667 2.5C12.8486 2.5 15.8333 5.48477 15.8333 9.16667Z" stroke="#132B3B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <input name="q" value="{{ $query['q'] ?? '' }}" placeholder="¿Qué estás buscando?" class="ml-[16px] min-w-0 grow bg-transparent text-[16px] font-normal leading-[24px] text-[#35363a] outline-none placeholder:text-[#35363a]">
        </label>

        <label class="flex max-w-full flex-col items-start gap-2 text-[15px] font-normal leading-normal text-[#262424] sm:flex-row sm:items-center sm:gap-0 sm:text-[16px] sm:whitespace-nowrap xl:absolute xl:top-[13px] xl:right-0">
            <span class="shrink-0">Filtrar por categoría:</span>
            <span class="relative inline-flex h-11 w-full min-w-0 items-center rounded-[6px] border border-[#dedfe0] px-3 pr-[36px] sm:ml-[12px] sm:h-6 sm:w-[198px] sm:shrink-0 sm:rounded-none sm:border-0 sm:px-0 sm:pr-[28px]">
                <span class="block truncate text-[16px] font-semibold leading-normal text-[#262424]">{{ $selectedCategory }}</span>
                <select name="category" onchange="this.form.submit()" aria-label="Filtrar por categoría" class="absolute inset-0 h-6 w-full cursor-pointer appearance-none opacity-0">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(($query['category'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true" class="pointer-events-none absolute right-0 size-[18px]"><path d="M4.5 6.75L9 11.25L13.5 6.75" stroke="#262424" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </span>
        </label>
    </form>

    <div class="mt-[27px] overflow-x-auto">
        <table class="min-w-[1224px] w-full table-fixed border-separate border-spacing-0 text-left">
            <thead class="h-[52px] bg-[#132b3b] text-center text-[16px] font-bold leading-[24px] text-white">
                <tr class="h-[52px]">
                    <th class="w-[252px] rounded-tl-[10px] px-[26px] py-0 text-center">Cod</th>
                    <th class="w-[180px] px-[17px] py-0 text-center">Nombre</th>
                    <th class="w-[95px] px-[17px] py-0 text-center">Precio</th>
                    <th class="w-[105px] px-[17px] py-0 text-center">Descuento</th>
                    <th class="w-[150px] px-[17px] py-0 text-center">Precio con descuento</th>
                    <th class="w-[95px] px-[17px] py-0 text-center">Stock físico</th>
                    <th class="w-[160px] px-[17px] py-0 text-center">Stock en tránsito</th>
                    <th class="w-[187px] rounded-tr-[10px] px-0 py-0 text-center">Cantidad</th>
                </tr>
            </thead>
            <tbody class="text-[16px] font-normal leading-[109px] text-[#262424]">
                @forelse($variants as $variant)
                    <tr class="h-[109px] bg-white">
                        <td class="border-b border-[#d8dcdd] p-0 font-normal text-[#262424]">
                            <div class="flex h-[109px] items-center gap-[12px] px-[12px]">
                                @if($variant->product->main_image)
                                    <img src="{{ Storage::url($variant->product->main_image) }}" alt="" class="size-[85px] shrink-0 rounded-[6px] border border-[#eaeaea] object-cover">
                                @else
                                    <img src="/storage/products/product-placeholder.svg" alt="" class="size-[85px] shrink-0 rounded-[6px] border border-[#eaeaea] object-cover">
                                @endif
                                <span class="truncate text-[16px] font-normal leading-[24px]">{{ $variant->code }}</span>
                            </div>
                        </td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 whitespace-nowrap">{{ $variant->product->name }}</td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 text-center whitespace-nowrap">USD {{ number_format((float) ($variant->list_price ?? 0), 2, ',', '.') }}</td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 text-center whitespace-nowrap">{{ $variant->discount_percent ? '-'.rtrim(rtrim((string) $variant->discount_percent, '0'), '.').'%' : '—' }}</td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 text-center whitespace-nowrap">USD {{ number_format((float) ($variant->final_price ?? $variant->list_price ?? 0), 2, ',', '.') }}</td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 text-center font-bold whitespace-nowrap">{{ $variant->physical_stock ?? 0 }}</td>
                        <td class="border-b border-[#d8dcdd] px-[17px] py-0 text-center whitespace-nowrap">{{ $variant->transit_stock ? $variant->transit_stock : '—' }}</td>
                        <td class="align-middle border-b border-[#d8dcdd] py-0 pr-[7px] leading-none">
                            <form action="{{ route('client.cart.add', $variant) }}" method="POST" class="flex items-center justify-end gap-[30px] leading-none">
                                @csrf
                                <div data-quantity-control class="relative h-[48px] w-[78px] shrink-0 rounded-[4px] border border-[#dedfe0] bg-white">
                                    <input data-quantity-input aria-label="Cantidad para {{ $variant->code }}" name="quantity" type="number" min="0" max="999" value="0" class="block h-[46px] w-[76px] appearance-none bg-transparent pl-[16px] pr-[28px] text-left text-[15px] font-normal leading-[46px] text-[#262424] outline-none [font-family:'Neue_Haas_Grotesk_Text_Pro',Montserrat,sans-serif] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    <div class="absolute right-[11px] top-[8px] flex flex-col gap-[4px]">
                                        <button type="button" data-quantity-step="1" aria-label="Aumentar cantidad" class="grid size-[12px] place-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M9 7.5L6 4.5L3 7.5" stroke="#D8DCDD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <button type="button" data-quantity-step="-1" aria-label="Disminuir cantidad" class="grid size-[12px] place-items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="#D8DCDD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <button aria-label="Agregar {{ $variant->code }} al carrito" class="flex size-[40px] shrink-0 items-center justify-center rounded-[12px] border border-[#1954b7] px-[10px] py-[8px] transition hover:bg-[#f4f8ff]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="16" viewBox="0 0 15 16" fill="none" aria-hidden="true"><path d="M4.50416 16C4.09128 16 3.73795 15.8435 3.44418 15.5304C3.15041 15.2173 3.00327 14.8405 3.00277 14.4C3.00277 13.96 3.14991 13.5835 3.44418 13.2704C3.73845 12.9573 4.09178 12.8005 4.50416 12.8C4.91704 12.8 5.27062 12.9568 5.56489 13.2704C5.85916 13.584 6.00605 13.9605 6.00555 14.4C6.00555 14.84 5.85866 15.2168 5.56489 15.5304C5.27112 15.844 4.91754 16.0005 4.50416 16ZM12.0111 16C11.5982 16 11.2449 15.8435 10.9511 15.5304C10.6573 15.2173 10.5102 14.8405 10.5097 14.4C10.5097 13.96 10.6568 13.5835 10.9511 13.2704C11.2454 12.9573 11.5987 12.8005 12.0111 12.8C12.424 12.8 12.7776 12.9568 13.0718 13.2704C13.3661 13.584 13.513 13.9605 13.5125 14.4C13.5125 14.84 13.3656 15.2168 13.0718 15.5304C12.7781 15.844 12.4245 16.0005 12.0111 16ZM3.86607 3.2L5.66774 7.2H10.9226L12.987 3.2H3.86607ZM3.15291 1.6H14.2256C14.5134 1.6 14.7324 1.7368 14.8825 2.0104C15.0326 2.284 15.0389 2.56053 14.9013 2.84L12.2363 7.96C12.0987 8.22667 11.9143 8.43333 11.683 8.58C11.4518 8.72667 11.1983 8.8 10.9226 8.8H5.32992L4.50416 10.4H13.5125V12H4.50416C3.94114 12 3.51575 11.7368 3.22798 11.2104C2.94022 10.684 2.9277 10.1605 3.19045 9.64L4.20388 7.68L1.50139 1.6H0V0H2.43975L3.15291 1.6Z" fill="#1954B7"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="border-b border-[#d8dcdd] px-4 py-14 text-center text-[#65747c]">No encontramos productos para esta búsqueda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($variants->hasPages())
        <div class="mt-8 flex flex-col items-center justify-between gap-4 pt-5 text-[#52636b] sm:flex-row">
            <p class="text-[13px] leading-5">Mostrando <strong class="font-semibold text-[#132b3b]">{{ $variants->firstItem() }}–{{ $variants->lastItem() }}</strong> de {{ $variants->total() }} productos</p>
            <nav aria-label="Paginación de productos" class="flex items-center gap-1.5">
                @if($variants->onFirstPage())
                    <span aria-disabled="true" class="grid size-9 cursor-not-allowed place-items-center rounded-[9px] border border-[#dfe5e8] text-[#aab4b9]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                @else
                    <a href="{{ $variants->previousPageUrl() }}" rel="prev" aria-label="Página anterior" class="grid size-9 place-items-center rounded-[9px] border border-[#dfe5e8] text-[#132b3b] transition hover:border-[#1954b7] hover:bg-[#edf3f7]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @endif

                @foreach(range(max(1, $variants->currentPage() - 2), min($variants->lastPage(), $variants->currentPage() + 2)) as $page)
                    <a href="{{ $variants->url($page) }}" aria-label="Ir a página {{ $page }}" @class(['grid size-9 place-items-center rounded-[9px] text-[13px] font-semibold transition', 'bg-[#132b3b] text-white shadow-[0_5px_12px_rgba(19,43,59,.18)]' => $page === $variants->currentPage(), 'border border-[#dfe5e8] text-[#52636b] hover:border-[#1954b7] hover:bg-[#edf3f7]' => $page !== $variants->currentPage()])>{{ $page }}</a>
                @endforeach

                @if($variants->hasMorePages())
                    <a href="{{ $variants->nextPageUrl() }}" rel="next" aria-label="Página siguiente" class="grid size-9 place-items-center rounded-[9px] border border-[#dfe5e8] text-[#132b3b] transition hover:border-[#1954b7] hover:bg-[#edf3f7]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @else
                    <span aria-disabled="true" class="grid size-9 cursor-not-allowed place-items-center rounded-[9px] border border-[#dfe5e8] text-[#aab4b9]">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                @endif
            </nav>
        </div>
    @endif
</section>

<script>
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-quantity-step]');
        if (!button) return;

        const input = button.closest('[data-quantity-control]')?.querySelector('[data-quantity-input]');
        if (!input) return;

        const current = Number.parseInt(input.value || '0', 10) || 0;
        const step = Number.parseInt(button.dataset.quantityStep || '0', 10) || 0;
        input.value = Math.max(0, Math.min(999, current + step));
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
</script>
@endsection

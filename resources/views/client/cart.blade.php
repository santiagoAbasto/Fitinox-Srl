@extends('layouts.client')

@section('title', 'Carrito | Zona Clientes')

@section('content')
<section class="fitinox-content-1224 min-h-[720px] pt-[24px] pb-[96px]">
    <nav aria-label="Migas de pan" class="text-[14px] leading-[21px] text-[#35363a]">
        <button type="button" data-private-public-link class="font-semibold hover:underline">Inicio</button><span class="px-1">&gt;</span><span>Carrito</span>
    </nav>

    @php($subtotal = $items->sum('list_total'))
    @php($total = $items->sum('total'))
    @php($tax = round($total * .21, 2))
    @php($totalWithTax = $total + $tax)

    <div class="mt-[58px] overflow-x-auto">
        <table class="min-w-[1224px] w-full table-fixed border-separate border-spacing-0 text-left">
            <thead class="h-[52px] bg-[#132b3b] text-center text-[16px] font-bold leading-[24px] text-white">
                <tr class="h-[52px]">
                    <th class="w-[250px] rounded-tl-[10px] px-3 py-0">Cod</th>
                    <th class="w-[155px] px-3 py-0">Nombre</th>
                    <th class="w-[92px] px-3 py-0">Precio</th>
                    <th class="w-[96px] px-3 py-0">Descuento</th>
                    <th class="w-[142px] px-3 py-0">Precio con desc.</th>
                    <th class="w-[94px] px-3 py-0">Stock físico</th>
                    <th class="w-[158px] px-3 py-0">Stock en tránsito</th>
                    <th class="w-[150px] px-3 py-0">Cantidad</th>
                    <th class="w-[64px] rounded-tr-[10px] px-0 py-0"><span class="sr-only">Eliminar</span></th>
                </tr>
            </thead>
            <tbody class="text-[16px] font-normal leading-[109px] text-[#262424]">
                @forelse($items as $item)
                    @php($variant = $item['variant'])
                    <tr class="h-[109px] bg-white">
                        <td class="border-b border-[#d8dcdd] p-0">
                            <div class="flex h-[109px] items-center gap-[12px] px-[12px]">
                                @if($variant->product->main_image)
                                    <img src="{{ Storage::url($variant->product->main_image) }}" alt="" class="size-[85px] shrink-0 rounded-[6px] border border-[#eaeaea] object-cover">
                                @else
                                    <img src="/storage/products/product-placeholder.svg" alt="" class="size-[85px] shrink-0 rounded-[6px] border border-[#eaeaea] object-cover">
                                @endif
                                <span class="truncate text-[16px] leading-[24px]">{{ $variant->code }}</span>
                            </div>
                        </td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 whitespace-nowrap">{{ $variant->product->name }}</td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 text-center whitespace-nowrap">USD {{ number_format($item['list_price'], 2, ',', '.') }}</td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 text-center whitespace-nowrap">{{ $item['discount'] ? '-'.rtrim(rtrim((string) $item['discount'], '0'), '.').'%' : '—' }}</td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 text-center whitespace-nowrap">USD {{ number_format($item['price'], 2, ',', '.') }}</td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 text-center font-bold whitespace-nowrap">{{ $variant->physical_stock ?? 0 }}</td>
                        <td class="border-b border-[#d8dcdd] px-3 py-0 text-center leading-[20px]">
                            @if($variant->transit_stock)
                                <strong class="block text-[16px] leading-[21px]">{{ $variant->transit_stock }}</strong>
                                <span class="mt-1 block text-[13px] font-normal leading-[19px] text-[#4f565a]">En tránsito</span>
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="border-b border-[#d8dcdd] py-0">
                            <form data-cart-quantity-form action="{{ route('client.cart.update', $variant) }}" method="POST" class="flex items-center justify-center leading-none">@csrf @method('PATCH')
                                <div data-quantity-control class="relative h-[48px] w-[78px] rounded-[4px] border border-[#dedfe0] bg-white">
                                    <input data-quantity-input onchange="this.form.submit()" aria-label="Cantidad {{ $variant->code }}" name="quantity" type="number" min="0" max="999" value="{{ $item['quantity'] }}" class="block h-[46px] w-[76px] appearance-none bg-transparent pl-[16px] pr-[28px] text-left text-[15px] font-normal leading-[46px] text-[#262424] outline-none [font-family:'Neue_Haas_Grotesk_Text_Pro',Montserrat,sans-serif] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                                    <div class="absolute right-[11px] top-[8px] flex flex-col gap-[4px]">
                                        <button type="button" data-cart-quantity-step="1" aria-label="Aumentar cantidad" class="grid size-[12px] place-items-center"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M9 7.5L6 4.5L3 7.5" stroke="#D8DCDD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                        <button type="button" data-cart-quantity-step="-1" aria-label="Disminuir cantidad" class="grid size-[12px] place-items-center"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="#D8DCDD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                    </div>
                                </div>
                            </form>
                        </td>
                        <td class="border-b border-[#d8dcdd] py-0 pr-[7px] leading-none">
                            <form action="{{ route('client.cart.update', $variant) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="quantity" value="0"><button type="submit" aria-label="Eliminar {{ $variant->code }}" class="flex size-[40px] items-center justify-center gap-[10px] rounded-[12px] border border-[#1954b7] px-[10px] py-[8px] transition hover:bg-[#1954b7] hover:[&>svg_path]:stroke-white"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.28531 8.571L7.42831 20H16.5703L17.7133 8.571M13.4993 15.5V10.5M10.4993 15.5V10.5M4.57031 6.286H9.14231M9.14231 6.286L9.52431 4.757C9.57848 4.54075 9.70336 4.34881 9.8791 4.21166C10.0548 4.0745 10.2714 4.00001 10.4943 4H13.5043C13.7272 4.00001 13.9438 4.0745 14.1195 4.21166C14.2953 4.34881 14.4201 4.54075 14.4743 4.757L14.8563 6.286M9.14231 6.286H14.8563M14.8563 6.286H19.4283" stroke="#1954B7" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="border-b border-[#d8dcdd] px-4 py-14 text-center"><p class="text-[#65747c]">Tu carrito está vacío.</p><a href="{{ route('client.products') }}" class="mt-4 inline-block font-semibold text-[#1954b7] hover:underline">Ver productos</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <a href="{{ route('client.products') }}" class="mt-[24px] inline-flex h-[40px] items-center justify-center rounded-[7px] border border-[#132b3b] px-[20px] text-[12px] font-semibold text-[#132b3b] transition hover:-translate-y-px hover:bg-[#edf3f7] hover:shadow-sm">SEGUIR COMPRANDO</a>

    <form action="{{ route('client.checkout') }}" method="POST" enctype="multipart/form-data" class="mt-[72px] grid gap-[25px] lg:grid-cols-[600px_minmax(0,1fr)]">@csrf
        <div>
            <article class="h-[240px] w-[600px] max-w-full overflow-hidden rounded-[6px] border border-[#dedfe0] bg-white">
                <h1 class="h-[61px] border-b border-[#dedfe0] px-[27px] pt-[20px] pb-[14px] text-[22px] font-semibold leading-normal text-[#35363a]">Información importante</h1>
                <div class="px-[27px] pt-[17px] text-[#35363a]"><p class="text-[16px] font-bold leading-[28.8px]">Venta sujeta a disponibilidad en stock</p><p class="w-[551px] text-[15px] font-normal leading-[27px]">Los precios se encuentran expresados en dólares</p></div>
            </article>
            <label class="mt-[34px] block text-[16px] font-normal leading-[24px] text-[#35363a]">¿Querés dejarnos un mensaje?
                <textarea name="notes" rows="4" placeholder="Días especiales de entrega, cambios de domicilio, expresos, requerimientos especiales en la mercadería, exenciones." class="mt-[15px] block h-[92px] w-[600px] max-w-full resize-none rounded-[6px] border border-[#dedfe0] px-[16px] py-[13px] text-[14px] leading-[18px] outline-none transition placeholder:text-[#667077] focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/10">{{ old('notes') }}</textarea>
                @error('notes')<span class="mt-1 block text-[12px] text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="mt-[30px] block text-[16px] font-normal leading-[24px] text-[#35363a]">¿Querés subir un archivo?
                <span class="mt-[15px] flex h-[48px] w-[600px] max-w-full cursor-pointer items-center rounded-[6px] border border-[#dedfe0] bg-white px-[16px] text-[14px] font-normal leading-normal text-[#35363a] transition hover:border-[#1954b7] hover:bg-[#f8fbff]">
                    <span data-file-name class="grow truncate">Seleccionar archivo</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="ml-3 size-6 shrink-0"><path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15M7 8L12 3L17 8M12 3V15" stroke="#1954B7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <input data-file-input name="attachment" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg" class="sr-only">
                </span>
            </label>
            <p class="mt-2 text-[12px] leading-[18px] text-[#78868d]">PDF, Word, Excel o imagen · máximo 10 MB</p>
            @error('attachment')<span class="mt-1 block text-[12px] text-red-600">{{ $message }}</span>@enderror
        </div>

        <div>
            <article class="h-[335px] w-[599px] max-w-full overflow-hidden rounded-[6px] border border-[#dedfe0] bg-white">
                <h2 class="h-[61px] border-b border-[#dedfe0] px-[27px] pt-[20px] pb-[14px] text-[22px] font-semibold leading-normal text-[#35363a]">Entrega</h2>
                <div class="px-[27px] pt-[35px]">
                    <label class="flex cursor-pointer items-center gap-[21px] text-[16px] font-normal leading-normal text-[#35363a]"><input type="radio" name="delivery_method" value="pickup" class="peer sr-only" @checked(old('delivery_method') === 'pickup')><span class="relative size-5 shrink-0 rounded-full border border-[#dedfe0] peer-checked:border-[#1954b7] after:absolute after:inset-[4px] after:hidden after:rounded-full after:bg-[#1954b7] peer-checked:after:block"></span><span>Retiro en fábrica</span></label>
                    <label class="mt-[18px] flex cursor-pointer items-center gap-[21px] text-[16px] font-normal leading-normal text-[#35363a]"><input type="radio" name="delivery_method" value="transport" class="peer sr-only" @checked(old('delivery_method', 'transport') === 'transport')><span class="relative size-5 shrink-0 rounded-full border border-[#dedfe0] peer-checked:border-[#1954b7] after:absolute after:inset-[4px] after:hidden after:rounded-full after:bg-[#1954b7] peer-checked:after:block"></span><span>Entrega a transporte</span></label>
                    <div data-transport-fields class="ml-[41px] mt-[15px] grid w-[506px] max-w-[calc(100%-41px)] gap-[9px]">
                        <input name="transport_name" value="{{ old('transport_name') }}" placeholder="Nombre del Transporte" class="h-[28px] rounded-[6px] border border-[#dedfe0] px-[12px] text-[12px] font-normal leading-normal text-[#35363a] outline-none transition placeholder:text-[#939393] focus:border-[#1954b7]">
                        <input name="transport_address" value="{{ old('transport_address') }}" placeholder="Dirección del transporte" class="h-[28px] rounded-[6px] border border-[#dedfe0] px-[12px] text-[12px] font-normal leading-normal text-[#35363a] outline-none transition placeholder:text-[#939393] focus:border-[#1954b7]">
                        <input name="transport_phone" value="{{ old('transport_phone') }}" placeholder="Teléfono del transporte" class="h-[28px] rounded-[6px] border border-[#dedfe0] px-[12px] text-[12px] font-normal leading-normal text-[#35363a] outline-none transition placeholder:text-[#939393] focus:border-[#1954b7]">
                        <input name="delivery_destination" value="{{ old('delivery_destination') }}" placeholder="Destino final" class="h-[28px] rounded-[6px] border border-[#dedfe0] px-[12px] text-[12px] font-normal leading-normal text-[#35363a] outline-none transition placeholder:text-[#939393] focus:border-[#1954b7]">
                    </div>
                    @error('delivery_method')<span class="mt-2 block text-[12px] text-red-600">{{ $message }}</span>@enderror
                </div>
            </article>

            <article class="mt-[24px] h-[453px] w-[599px] max-w-full overflow-hidden rounded-[6px] border border-[#dedfe0] bg-white">
                <h2 class="h-[61px] border-b border-[#dedfe0] px-[27px] pt-[20px] pb-[14px] text-[22px] font-semibold leading-normal text-[#35363a]">Tu pedido</h2>
                <div class="flex h-[131px] items-center justify-between border-b border-[#dedfe0] px-[27px] text-[16px] leading-[24px] text-[#35363a]"><span>{{ $items->sum('quantity') }} {{ $items->sum('quantity') === 1 ? 'Item' : 'Items' }}</span><strong>USD {{ number_format($total, 2, ',', '.') }}</strong></div>
                <div class="flex h-[81px] items-center justify-between border-b border-[#dedfe0] px-[27px] text-[16px] leading-[24px] text-[#4f565a]"><span>Subtotal</span><strong class="text-[#35363a]">USD {{ number_format($subtotal, 2, ',', '.') }}</strong></div>
                <div class="flex h-[114px] flex-col justify-center gap-[12px] border-b border-[#dedfe0] px-[27px] text-[16px] leading-[24px] text-[#4f565a]"><div class="flex justify-between"><span>Bonificaciones</span><span>A definir</span></div><div class="flex justify-between"><span>IVA (21%)</span><strong class="text-[#35363a]">USD {{ number_format($tax, 2, ',', '.') }}</strong></div><div class="flex justify-between"><span>Otros impuestos</span><span>No incluidos</span></div></div>
                <div class="flex h-[65px] items-center justify-between px-[27px] text-[16px] font-bold leading-[24px] text-[#35363a]"><span>TOTAL <small class="text-[12px]">(IVA incluido)</small></span><strong class="text-[20px]">USD {{ number_format($totalWithTax, 2, ',', '.') }}</strong></div>
            </article>
            <div class="mt-[26px] grid grid-cols-2 gap-[24px]"><a href="{{ route('client.products') }}" class="inline-flex h-[40px] items-center justify-center rounded-[7px] border border-[#132b3b] px-[20px] text-[12px] font-semibold text-[#132b3b] transition hover:-translate-y-px hover:bg-[#edf3f7]">CANCELAR PEDIDO</a><button @disabled($items->isEmpty()) class="inline-flex h-[40px] items-center justify-center rounded-[7px] bg-[#132b3b] px-[20px] text-[12px] font-semibold text-white transition hover:-translate-y-px hover:bg-[#1954b7] hover:shadow-[0_8px_20px_rgba(25,84,183,.22)] disabled:cursor-not-allowed disabled:opacity-45">REALIZAR PEDIDO</button></div>
        </div>
    </form>
</section>

<script>
    document.querySelector('[data-file-input]')?.addEventListener('change', function () {
        document.querySelector('[data-file-name]').textContent = this.files?.[0]?.name || 'Seleccionar archivo';
    });
    document.querySelectorAll('[data-cart-quantity-step]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = button.closest('[data-quantity-control]')?.querySelector('[data-quantity-input]');
            if (!input) return;
            const quantity = Math.max(0, Math.min(999, (Number.parseInt(input.value || '0', 10) || 0) + (Number.parseInt(button.dataset.cartQuantityStep || '0', 10) || 0)));
            input.value = quantity;
            input.closest('form')?.requestSubmit();
        });
    });
    const syncTransport = () => document.querySelector('[data-transport-fields]')?.classList.toggle('hidden', document.querySelector('input[name="delivery_method"]:checked')?.value === 'pickup');
    document.querySelectorAll('input[name="delivery_method"]').forEach(input => input.addEventListener('change', syncTransport));
    syncTransport();
</script>
@endsection

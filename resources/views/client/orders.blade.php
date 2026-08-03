@extends('layouts.client')

@section('title', 'Histórico de pedidos | Zona Clientes')

@section('content')
<section class="fitinox-content-1224 min-h-[620px] pt-[24px] pb-[96px]">
    <nav aria-label="Migas de pan" class="text-[14px] leading-[21px] text-[#35363a]">
        <button type="button" data-private-public-link class="font-semibold hover:underline">Inicio</button><span class="px-1">&gt;</span><span>Histórico de pedidos</span>
    </nav>

    <div class="mt-[58px] overflow-x-auto">
        <table class="min-w-[900px] w-full border-separate border-spacing-0 text-left">
            <thead class="h-[52px] bg-[#132b3b] text-center text-[16px] font-bold leading-[24px] text-white">
                <tr class="h-[52px]">
                    <th class="rounded-tl-[10px] px-[27px] text-left">N° de pedido</th>
                    <th class="w-[240px] px-4">Fecha de entrega</th>
                    <th class="w-[205px] px-4">Importe</th>
                    <th class="w-[333px] rounded-tr-[10px] px-[27px]"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="text-[16px] font-normal leading-[24px] text-[#35363a]">
                @forelse($orders as $order)
                    <tr class="h-[84px] bg-white transition hover:bg-[#f8fbff]">
                        <td class="h-[84px] border-b border-[#dedfe0] px-[27px] py-0">
                            <div class="flex items-center gap-[18px]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="43" height="43" viewBox="0 0 43 43" fill="none" class="size-[43px] shrink-0" aria-hidden="true"><path d="M10.7507 39.4167V7.16671C10.7507 6.21635 11.1282 5.30491 11.8002 4.63291C12.4722 3.9609 13.3836 3.58337 14.334 3.58337H28.6673C29.6177 3.58337 30.5291 3.9609 31.2011 4.63291C31.8731 5.30491 32.2507 6.21635 32.2507 7.16671V39.4167M10.7507 39.4167H32.2507M10.7507 39.4167H7.16732C6.21696 39.4167 5.30552 39.0392 4.63352 38.3672C3.96151 37.6952 3.58398 36.7837 3.58398 35.8334V25.0834C3.58398 24.133 3.96151 23.2216 4.63352 22.5496C5.30552 21.8776 6.21696 21.5 7.16732 21.5H10.7507M32.2507 39.4167H35.834C36.7843 39.4167 37.6958 39.0392 38.3678 38.3672C39.0398 37.6952 39.4173 36.7837 39.4173 35.8334V19.7084C39.4173 18.758 39.0398 17.8466 38.3678 17.1746C37.6958 16.5026 36.7843 16.125 35.834 16.125H32.2507M17.9173 10.75H25.084M17.9173 17.9167H25.084M17.9173 25.0834H25.084M17.9173 32.25H25.084" stroke="#1954B7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="font-medium tabular-nums">{{ $order->number ?: str_pad((string) $order->id, 9, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </td>
                        <td class="h-[84px] border-b border-[#dedfe0] px-4 py-0 text-center tabular-nums">{{ ($order->delivery_date ?? $order->created_at)->format('d/m/Y') }}</td>
                        <td class="h-[84px] border-b border-[#dedfe0] px-4 py-0 text-center font-semibold tabular-nums text-[#262424]">USD {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                        <td class="h-[84px] border-b border-[#dedfe0] px-[27px] py-0">
                            <div class="flex items-center justify-end gap-[12px]">
                                <a href="{{ route('client.orders.show', $order) }}" target="_blank" rel="noreferrer" class="inline-flex h-[40px] items-center justify-center rounded-[7px] border border-[#132b3b] px-[20px] text-[12px] font-semibold text-[#132b3b] transition hover:-translate-y-px hover:bg-[#edf3f7] hover:shadow-sm">VER ONLINE</a>
                                <a href="{{ route('client.orders.download', $order) }}" class="inline-flex h-[40px] items-center justify-center rounded-[7px] bg-[#132b3b] px-[20px] text-[12px] font-semibold text-white transition hover:-translate-y-px hover:bg-[#1954b7] hover:shadow-[0_8px_20px_rgba(25,84,183,.20)]">DESCARGAR</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="border-b border-[#dedfe0] px-4 py-14 text-center"><p class="text-[#65747c]">Todavía no registraste pedidos.</p><a href="{{ route('client.products') }}" class="mt-4 inline-block font-semibold text-[#1954b7] hover:underline">Ver productos</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <nav aria-label="Paginación de pedidos" class="mt-[32px] flex items-center justify-end gap-2">
            @if($orders->onFirstPage())
                <span aria-hidden="true" class="grid size-[40px] place-items-center rounded-[10px] border border-[#dedfe0] text-[#a4afb4]">‹</span>
            @else
                <a href="{{ $orders->previousPageUrl() }}" class="grid size-[40px] place-items-center rounded-[10px] border border-[#dedfe0] text-[22px] text-[#132b3b] transition hover:border-[#1954b7] hover:text-[#1954b7]" aria-label="Página anterior">‹</a>
            @endif
            @foreach($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" @class(['grid size-[40px] place-items-center rounded-[10px] border text-[14px] font-semibold transition', 'border-[#132b3b] bg-[#132b3b] text-white shadow-[0_8px_18px_rgba(19,43,59,.16)]' => $page === $orders->currentPage(), 'border-[#dedfe0] text-[#65747c] hover:border-[#1954b7] hover:text-[#1954b7]' => $page !== $orders->currentPage()])>{{ $page }}</a>
            @endforeach
            @if($orders->hasMorePages())
                <a href="{{ $orders->nextPageUrl() }}" class="grid size-[40px] place-items-center rounded-[10px] border border-[#dedfe0] text-[22px] text-[#132b3b] transition hover:border-[#1954b7] hover:text-[#1954b7]" aria-label="Página siguiente">›</a>
            @else
                <span aria-hidden="true" class="grid size-[40px] place-items-center rounded-[10px] border border-[#dedfe0] text-[#a4afb4]">›</span>
            @endif
        </nav>
    @endif
</section>
@endsection

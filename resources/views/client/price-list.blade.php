@extends('layouts.client')

@section('title', 'Lista de precios | Zona Clientes')

@section('content')
<section class="fitinox-content-1224 min-h-[620px] pt-[24px] pb-[96px]">
    <nav aria-label="Migas de pan" class="text-[14px] leading-[21px] text-[#35363a]">
        <button type="button" data-private-public-link class="font-semibold hover:underline">Inicio</button><span class="px-1">&gt;</span><span>Lista de precios</span>
    </nav>

    <div class="mt-[58px] overflow-x-auto">
        <table class="min-w-[900px] w-full border-separate border-spacing-0 text-left">
            <thead class="h-[52px] bg-[#132b3b] text-center text-[16px] font-bold leading-[24px] text-white">
                <tr class="h-[52px]"><th class="rounded-tl-[10px] px-[27px] text-left">Archivo</th><th class="w-[152px] px-4">Formato</th><th class="w-[150px] px-4">Peso</th><th class="w-[333px] rounded-tr-[10px] px-[27px]"><span class="sr-only">Acciones</span></th></tr>
            </thead>
            <tbody class="text-[16px] font-normal leading-[24px] text-[#35363a]">
                @forelse($priceLists as $priceList)
                    @php($extension = strtoupper(pathinfo($priceList->file_path, PATHINFO_EXTENSION)))
                    <tr class="h-[88px] bg-white transition hover:bg-[#f8fbff]">
                        <td class="border-b border-[#d8dcdd] px-[27px] py-0">
                            <div class="flex items-center gap-[18px]"><svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40" fill="none" class="size-10 shrink-0" aria-hidden="true"><path d="M23.3327 3.33337V10C23.3327 10.8841 23.6839 11.7319 24.309 12.3571C24.9341 12.9822 25.782 13.3334 26.666 13.3334H33.3327M24.9993 3.33337H9.99935C9.11529 3.33337 8.26745 3.68456 7.64233 4.30968C7.0172 4.93481 6.66602 5.78265 6.66602 6.66671V33.3334C6.66602 34.2174 7.0172 35.0653 7.64233 35.6904C8.26745 36.3155 9.11529 36.6667 9.99935 36.6667H29.9993C30.8834 36.6667 31.7312 36.3155 32.3564 35.6904C32.9815 35.0653 33.3327 34.2174 33.3327 33.3334V11.6667L24.9993 3.33337Z" stroke="#1954B7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="truncate font-medium">{{ $priceList->name }}</span></div>
                        </td>
                        <td class="border-b border-[#d8dcdd] px-4 py-0 text-center font-semibold">{{ $extension }}</td>
                        <td class="border-b border-[#d8dcdd] px-4 py-0 text-center">{{ $priceList->file_size ? number_format($priceList->file_size / 1024, 0).' kb' : '—' }}</td>
                        <td class="border-b border-[#d8dcdd] px-[27px] py-0"><div class="flex items-center justify-end gap-[12px]"><a href="{{ route('client.price-list.view', $priceList) }}" target="_blank" rel="noreferrer" class="inline-flex h-[40px] items-center justify-center rounded-[7px] border border-[#132b3b] px-[20px] text-[12px] font-semibold text-[#132b3b] transition hover:-translate-y-px hover:bg-[#edf3f7] hover:shadow-sm">VER ONLINE</a><a href="{{ route('client.price-list.download', $priceList) }}" class="inline-flex h-[40px] items-center justify-center rounded-[7px] bg-[#132b3b] px-[20px] text-[12px] font-semibold text-white transition hover:-translate-y-px hover:bg-[#1954b7] hover:shadow-[0_8px_20px_rgba(25,84,183,.20)]">DESCARGAR</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="border-b border-[#d8dcdd] px-4 py-14 text-center text-[#65747c]">Todavía no hay una lista de precios publicada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection

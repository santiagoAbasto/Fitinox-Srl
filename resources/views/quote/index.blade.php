@extends('layouts.public')
@section('title', $page->seo_title ?: 'Solicitud de presupuesto | Fitinox')
@section('meta_description', $page->seo_description ?: 'Solicitá un presupuesto personalizado de productos Fitinox.')
@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[300px]">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/quote/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Presupuesto</p>
        <h1 class="absolute bottom-10 left-0 max-w-full text-[30px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">{{ $page->hero_title ?: 'Solicitud de presupuesto' }}</h1>
    </div>
</section>

@if(session('success'))
<div id="quote-success" role="status" class="fixed top-[120px] right-6 z-[70] w-[min(390px,calc(100vw-48px))] overflow-hidden rounded-[20px] border border-white/70 bg-white/95 shadow-[0_24px_70px_rgba(7,19,27,.22)] backdrop-blur-xl">
    <div class="flex items-start gap-3 p-4">
        <span class="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#e9f8f0] text-[#17845c]">
            <svg class="size-6" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
        <div class="grow pt-0.5"><p class="text-[13px] font-semibold text-[#132b3b]">Solicitud enviada</p><p class="mt-1 text-[11px] leading-[17px] text-[#65747c]">{{ session('success') }}</p></div>
        <button type="button" onclick="this.closest('#quote-success').remove()" class="grid size-8 place-items-center rounded-full text-[#8b979d]" aria-label="Cerrar">×</button>
    </div>
</div>
@endif

<section class="mx-auto min-h-[1010px] w-[calc(100%-32px)] max-w-[1016px] py-12 sm:w-[calc(100%-40px)] sm:py-16 min-[1224px]:w-full min-[1224px]:py-[80px]">
    <h2 class="ml-px text-[32px] leading-[38px] font-medium text-[#132b3b]">Productos</h2>
    <div class="mx-px mt-[11px] min-h-[281px] border-t border-[#dedfe0]">
        @forelse($items as $item)
        <article class="relative grid min-h-[140px] items-center border-b border-[#dedfe0] py-[9px] max-xl:grid-cols-[96px_1fr_auto] max-xl:gap-4 xl:block">
            <div class="flex h-[121px] w-[121px] items-center justify-center overflow-hidden rounded-[4px] border border-[#dedfe0] bg-white max-xl:size-24 xl:absolute xl:top-[9px] xl:left-[-1px]">
                <img src="{{ Storage::url($item['image'] ?: 'products/product-placeholder.svg') }}" class="h-full w-full object-contain" alt="{{ $item['product_name'] }}">
            </div>
            <div class="max-xl:pl-0 xl:absolute xl:top-[29px] xl:left-[140px] xl:w-[240px]">
                <p class="text-[14px] leading-[21px] font-bold text-[#1954b7]">{{ mb_strtoupper($item['category_name']) }}</p>
                <h3 class="mt-[1px] text-[24px] leading-[29px] font-semibold text-black">{{ $item['product_name'] }}</h3>
            </div>
            <div class="text-center text-[16px] leading-[50px] text-[#35363a] max-xl:hidden xl:absolute xl:top-[29px] xl:left-[389px] xl:w-[65px]"><span class="block text-[18px] leading-[27px] font-semibold text-[#132b3b]">Tubo</span>{{ $item['tube'] }}</div>
            <div class="text-center text-[16px] leading-[50px] text-[#35363a] max-xl:hidden xl:absolute xl:top-[29px] xl:left-[500px] xl:w-[70px]"><span class="block text-[18px] leading-[27px] font-semibold text-[#132b3b]">Rosca</span>{{ $item['thread'] }}</div>
            <div class="text-[16px] leading-[50px] text-[#35363a] max-xl:hidden xl:absolute xl:top-[29px] xl:left-[623px] xl:w-[170px]"><span class="block text-[18px] leading-[27px] font-semibold text-[#132b3b]">Código</span>{{ $item['code'] }}</div>
            <form method="POST" action="{{ route('quote.items.update', $item['variant_id']) }}" class="max-xl:col-start-2 xl:absolute xl:top-[29px] xl:left-[804px] xl:w-[85px]">
                @csrf @method('PATCH')
                <label class="block text-[18px] leading-[27px] font-semibold text-[#132b3b]">Cantidad</label>
                <input type="number" name="quantity" min="1" max="999" value="{{ $item['quantity'] }}" onchange="this.form.submit()" class="mt-[5px] h-[33px] w-[85px] rounded-[4px] border border-[#dedfe0] px-[15px] text-left text-[16px] outline-none focus:border-[#1954b7]">
            </form>
            <form method="POST" action="{{ route('quote.items.destroy', $item['variant_id']) }}" class="max-xl:row-span-2 max-xl:row-start-1 max-xl:col-start-3 xl:absolute xl:top-[50px] xl:left-[950px]">
                @csrf @method('DELETE')
                <button type="submit" class="grid size-10 place-items-center rounded-[12px] border border-[#1954b7]" aria-label="Quitar {{ $item['product_name'] }}"><img src="/storage/figma/quote/trash.svg" class="size-6" alt=""></button>
            </form>
        </article>
        @empty
        <div class="flex min-h-[200px] flex-col items-start justify-center gap-6 rounded-[14px] border border-[#dbe3e7] bg-[#f8fafb] p-6 sm:p-8 min-[1224px]:min-h-[140px] min-[1224px]:flex-row min-[1224px]:items-center min-[1224px]:justify-between min-[1224px]:rounded-none min-[1224px]:border-x-0 min-[1224px]:border-t-0 min-[1224px]:bg-transparent min-[1224px]:p-0">
            <div><p class="text-[18px] font-semibold text-[#132b3b] min-[1224px]:text-[16px] min-[1224px]:font-medium">Todavía no agregaste productos.</p><p class="mt-2 max-w-[440px] text-[14px] leading-6 text-[#6d777c] min-[1224px]:text-[13px] min-[1224px]:leading-normal">Elegí una especificación técnica y presioná “Consultar”.</p></div>
            <a href="/productos" class="inline-flex min-h-12 w-full items-center justify-center rounded-[9px] border border-[#132b3b] bg-white px-6 text-[13px] font-semibold tracking-[.03em] text-[#132b3b] min-[1224px]:h-[36px] min-[1224px]:min-h-0 min-[1224px]:w-auto min-[1224px]:rounded-[7px]">VER PRODUCTOS</a>
        </div>
        @endforelse
    </div>

    <h2 class="ml-px mt-[42px] text-[32px] leading-[38px] font-medium text-[#132b3b]">Datos personales</h2>
    <form method="POST" action="{{ route('quote.store') }}" class="mt-[24px] grid gap-x-[24px] gap-y-[24px] lg:grid-cols-2">
        @csrf
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true"><input type="hidden" name="form_started_at" value="{{ now()->timestamp }}">
        @php($field = 'mt-[5px] h-[45px] w-full rounded-[4px] border border-[#dedfe0] bg-white px-[12px] text-[16px] text-[#35363a] outline-none transition focus:border-[#1954b7] focus:ring-2 focus:ring-[#1954b7]/10')
        <label class="text-[16px] leading-[24px] font-normal text-[#35363a]">Nombre y Apellido*
            <input name="name" value="{{ old('name') }}" required class="{{ $field }}">
            @error('name')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror
        </label>
        <label class="text-[16px] leading-[24px] font-normal text-[#35363a]">E-mail*
            <input type="email" name="email" value="{{ old('email') }}" required class="{{ $field }}">
            @error('email')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror
        </label>
        <label class="text-[16px] leading-[24px] font-normal text-[#35363a]">Teléfono*
            <input name="phone" value="{{ old('phone') }}" required class="{{ $field }}">
            @error('phone')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror
        </label>
        <label class="text-[16px] leading-[24px] font-normal text-[#35363a]">Empresa
            <input name="company" value="{{ old('company') }}" class="{{ $field }}">
        </label>
        <label class="text-[16px] leading-[24px] font-normal text-[#35363a]">Observaciones / Aclaraciones
            <textarea name="notes" class="mt-[5px] h-[157px] w-full resize-none rounded-[4px] border border-[#dedfe0] bg-white p-[12px] text-[16px] leading-[24px] outline-none transition focus:border-[#1954b7] focus:ring-2 focus:ring-[#1954b7]/10">{{ old('notes') }}</textarea>
        </label>
        <div class="relative ml-0 h-[198px]">
            @if(config('services.turnstile.site_key'))<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="quote"></div>@endif
            <p class="absolute top-[158px] left-0 text-[16px] leading-[24px] text-[#35363a]">*Datos obligatorios</p>
            @error('cart')<p class="absolute top-[125px] left-0 text-[11px] text-red-600">{{ $message }}</p>@enderror
            <button type="submit" {{ $items->isEmpty() ? 'disabled' : '' }} class="absolute top-[148px] right-0 inline-flex h-[36px] w-[170px] items-center justify-center gap-[10px] whitespace-nowrap rounded-[7px] bg-[#132b3b] px-5 py-2 text-[13px] font-medium tracking-[.03em] text-white shadow-none transition-[background-color,box-shadow,transform] duration-200 ease-out hover:-translate-y-px hover:bg-[#1954b7] hover:shadow-[0_8px_20px_rgba(25,84,183,.24)] focus-visible:outline-2 focus-visible:outline-offset-3 focus-visible:outline-[#1954b7] active:translate-y-0 active:shadow-none disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:translate-y-0 disabled:hover:bg-[#132b3b] disabled:hover:shadow-none">ENVIAR SOLICITUD</button>
        </div>
    </form>
</section>

<script>
window.setTimeout(() => document.getElementById('quote-success')?.remove(), 5200);
</script>
@endsection

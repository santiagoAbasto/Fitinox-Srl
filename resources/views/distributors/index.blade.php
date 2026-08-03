@extends('layouts.public')
@section('title', $page->seo_title ?: 'Distribuidores | Fitinox')
@section('meta_description', $page->seo_description ?: 'Encontrá distribuidores oficiales de Fitinox en Argentina.')
@section('content')
<section class="relative h-[300px] overflow-hidden">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/distributors/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-80 mix-blend-multiply"></div>
    <div data-hero-content class="relative mx-auto h-full w-[calc(100%-40px)] max-w-[1224px] text-white xl:w-full">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Distribuidores</p>
        <h1 class="absolute top-[202px] left-0 w-[529px] text-[40px] leading-[48px] font-semibold">{{ $page->hero_title ?: 'Distribuidores' }}</h1>
    </div>
</section>

<section class="mx-auto w-[calc(100%-40px)] max-w-[1224px] py-[80px] lg:h-[950px] lg:py-0 lg:pt-[80px] xl:w-full">
    <div class="grid gap-8 lg:grid-cols-[392px_809px] lg:gap-[24px]">
        <div class="relative h-[776px] overflow-hidden pr-[19px]">
            <div class="flex items-center">
                <label class="flex h-[44px] w-[345px] items-center border border-[#dedfe0] bg-white px-[10px]">
                    <input id="distributor-search" type="search" value="Argentina" aria-label="Buscar distribuidores" class="w-full bg-transparent text-[16px] leading-5 text-[#35363a] outline-none">
                </label>
                <button id="distributor-search-button" type="button" aria-label="Buscar" class="ml-[17px] grid size-6 shrink-0 place-items-center"><img src="/storage/figma/distributors/search.svg" class="size-6" alt=""></button>
            </div>

            <div id="distributor-list" class="mt-[23px] h-[643px] overflow-y-auto pr-[11px] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($distributors as $index => $distributor)
                    <article data-distributor data-search="{{ mb_strtolower($distributor->country.' '.$distributor->name.' '.$distributor->address) }}" data-index="{{ $index }}" class="border-b border-[#dedfe0] px-[8px] pb-[19px] {{ $index ? 'pt-[19px]' : '' }}">
                        <button type="button" data-select-distributor="{{ $distributor->id }}" class="block w-full text-left">
                            <h2 class="max-w-[366px] whitespace-pre-line text-[18px] leading-[22px] font-normal text-[#1d1d1d]">{{ $distributor->name }}</h2>
                            <div class="mt-[3px] text-[14px] leading-[22px] text-[#1d1d1d]">
                                <p>{{ $distributor->address }}</p>
                                @if($distributor->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', strtok($distributor->phone, "\n")) }}" class="block whitespace-pre-line hover:text-[#1954b7]">{{ $distributor->phone }}</a>@endif
                                @if($distributor->email)<a href="mailto:{{ $distributor->email }}" class="block hover:text-[#1954b7]">{{ $distributor->email }}</a>@endif
                            </div>
                        </button>
                    </article>
                @endforeach
                <button id="load-more-distributors" type="button" class="h-[61px] w-full text-center text-[16px] tracking-[.96px] text-[#1d1d1d]">Cargar más resultados</button>
                <p id="no-distributors" class="hidden py-12 text-center text-[14px] text-[#667780]">No encontramos distribuidores.</p>
            </div>
            <div id="distributor-scroll-track" class="absolute top-[68px] right-0 hidden h-[704px] w-[8px] rounded-full bg-[#efefef]">
                <div id="distributor-scroll-thumb" class="w-[8px] rounded-full bg-[#d7d7d7]"></div>
            </div>
        </div>

        <div id="distributor-map-shell" class="relative h-[776px]">
            <div id="distributor-map" class="relative z-0 h-full overflow-hidden bg-[#e7e7e7]" aria-label="Mapa interactivo de distribuidores"></div>
            <div class="absolute top-[18px] right-[18px] z-[500] flex flex-col items-end">
                <button id="use-my-location" type="button" class="inline-flex h-[42px] items-center gap-[8px] rounded-[7px] border border-[#dedfe0] bg-white px-[14px] text-[11px] font-semibold text-[#132b3b] shadow-[0_5px_20px_rgba(19,43,59,.16)] transition hover:border-[#1954b7]">
                    <span class="text-[20px] leading-none text-[#1954b7]">⌖</span>
                    <span>USAR MI UBICACIÓN</span>
                </button>
                <p id="nearest-location-status" class="mt-2 hidden max-w-[290px] rounded-[7px] bg-white/95 px-3 py-2 text-right text-[10px] leading-[15px] text-[#52636c] shadow-[0_8px_24px_rgba(19,43,59,.14)] backdrop-blur"></p>
            </div>
        </div>
    </div>
</section>

@php
    $mapLocations = $distributors->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name,
        'address' => $item->address,
        'phone' => $item->phone,
        'email' => $item->email,
        'latitude' => $item->latitude,
        'longitude' => $item->longitude,
    ])->values();

    if (app()->getLocale() === 'en') {
        $sourceTexts = $mapLocations->flatMap(fn($item) => [$item['name'], $item['address']])->filter()->values()->all();
        $translations = app(\App\Services\DeepLTranslationService::class)->translateMany($sourceTexts);
        $mapLocations = $mapLocations->map(fn($item) => [
            ...$item,
            'name' => $translations[$item['name']] ?? $item['name'],
            'address' => $translations[$item['address']] ?? $item['address'],
        ]);
    }
@endphp
<script>window.FitinoxDistributorLocations = {{ Illuminate\Support\Js::from($mapLocations) }};</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('distributor-search');
    const rows = [...document.querySelectorAll('[data-distributor]')];
    const empty = document.getElementById('no-distributors');
    const list = document.getElementById('distributor-list');
    const track = document.getElementById('distributor-scroll-track');
    const thumb = document.getElementById('distributor-scroll-thumb');
    const loadMore = document.getElementById('load-more-distributors');
    let limit = 4;
    const filter = () => {
        const term = input.value.trim().toLocaleLowerCase();
        let visible = 0;
        rows.forEach(row => {
            const matches = !term || row.dataset.search.includes(term);
            const show = matches && visible < limit;
            row.classList.toggle('hidden', !show);
            if (matches) visible++;
        });
        const matches = rows.filter(row => !term || row.dataset.search.includes(term)).length;
        empty.classList.toggle('hidden', matches > 0);
        loadMore.classList.toggle('hidden', matches <= limit);
        requestAnimationFrame(syncThumb);
    };
    const syncThumb = () => {
        const range = list.scrollHeight - list.clientHeight;
        const hasOverflow = range > 2;
        track.classList.toggle('hidden', !hasOverflow);
        if (!hasOverflow) {
            thumb.style.height = '0px';
            thumb.style.transform = 'translateY(0)';
            return;
        }
        const trackHeight = track.clientHeight;
        const thumbHeight = Math.max(72, Math.round(trackHeight * (list.clientHeight / list.scrollHeight)));
        const travel = trackHeight - thumbHeight;
        thumb.style.height = `${thumbHeight}px`;
        thumb.style.transform = `translateY(${(list.scrollTop / range) * travel}px)`;
    };
    document.getElementById('distributor-search-button').addEventListener('click', () => { limit = 4; filter(); });
    input.addEventListener('input', () => { limit = 4; filter(); });
    loadMore.addEventListener('click', () => { limit += 4; filter(); });
    list.addEventListener('scroll', syncThumb, { passive: true });
    document.querySelectorAll('[data-select-distributor]').forEach(button => button.addEventListener('click', () => {
        window.dispatchEvent(new CustomEvent('fitinox:select-distributor', {detail:{id:Number(button.dataset.selectDistributor)}}));
    }));
    filter(); syncThumb();
});
</script>
@endsection

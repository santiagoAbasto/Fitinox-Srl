@extends('layouts.public')
@section('title', $product->name.' | Fitinox')
@section('content')
<div class="mx-auto w-[calc(100%-40px)] max-w-[1224px] py-[24px] xl:w-full">
    <p class="text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; <a href="/productos" class="font-bold">Productos</a> &gt; <a href="{{ route('products.category',$product->category) }}" class="font-bold">{{ $product->category->name }}</a> &gt; {{ $product->name }}</p>
    <section data-reveal class="mt-[76px] grid gap-[26px] lg:grid-cols-[600px_1fr]">
        <div class="grid grid-cols-[80px_1fr] gap-[24px]">
            <div class="space-y-[10px]">@foreach(array_slice(array_values(array_filter(array_merge([$product->main_image], $product->gallery ?? []))),0,3) as $image)<button class="size-[80px] overflow-hidden rounded-[4px] border {{ $loop->first ? 'border-2 border-fitinox' : 'border-[#dedfe0]' }}"><img src="{{ Storage::url($image) }}" class="h-full w-full object-cover" alt=""></button>@endforeach</div>
            <div class="group relative h-[496px] overflow-hidden rounded-[4px] border border-[#dedfe0]" data-product-media>
                @if($product->video_type === 'youtube' && $product->youtube_url)<iframe class="h-full w-full" src="{{ $product->youtube_url }}{{ str_contains($product->youtube_url, '?') ? '&' : '?' }}autoplay=1&mute=1&controls=0&modestbranding=1&playsinline=1" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                @elseif($product->video_type === 'upload' && $product->video_path)
                    <video class="h-full w-full object-cover" autoplay muted playsinline preload="auto" data-product-video>
                        <source src="{{ Storage::url($product->video_path) }}">
                    </video>
                    <button
                        type="button"
                        class="absolute top-1/2 left-1/2 z-10 grid size-[54px] -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-[#132b3b]/80 text-white opacity-0 shadow-[0_8px_24px_rgba(0,0,0,.22)] backdrop-blur-sm transition-[opacity,transform,background-color] duration-200 hover:scale-105 hover:bg-[#132b3b] focus-visible:opacity-100 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                        aria-label="Pausar video"
                        data-video-toggle
                    >
                        <svg data-icon-play class="hidden size-[22px]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M8 5.75V18.25L18 12L8 5.75Z" fill="currentColor"/>
                        </svg>
                        <svg data-icon-pause class="size-[22px]" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M7 5.5H10V18.5H7V5.5ZM14 5.5H17V18.5H14V5.5Z" fill="currentColor"/>
                        </svg>
                    </button>
                @else<img src="{{ Storage::url($product->main_image ?: 'products/product-placeholder.svg') }}" class="h-full w-full object-contain" alt="{{ $product->name }}">@endif
                <span class="absolute right-[16px] bottom-[16px] grid size-[42px] place-items-center rounded-full bg-black/35 backdrop-blur-[2px]" title="{{ $product->video_type ? 'Vista 360° disponible' : 'Vista 360° próximamente' }}"><img src="/storage/figma/products/view-360.svg" alt="Vista 360 grados" class="h-[30px] w-[30px] shrink-0"></span>
            </div>
        </div>
        <div class="relative h-[496px]"><p class="text-[12px] font-bold text-[#1954b7]">{{ mb_strtoupper($product->category->name) }}</p><h1 class="mt-[25px] border-b border-[#dedfe0] pb-[16px] text-[32px] leading-[38px] font-medium text-fitinox">{{ $product->name }}</h1>@if($product->description)<div class="mt-6 max-h-[300px] overflow-y-auto pr-2 text-[16px] leading-[24px]">{!! $product->description !!}</div>@endif<a href="{{ route('products.datasheet', $product) }}" target="_blank" class="absolute bottom-0 left-0 inline-flex h-[36px] w-[280px] items-center justify-center rounded-[7px] border border-fitinox bg-white px-5 text-[13px] font-medium tracking-[.03em] text-fitinox">FICHA TÉCNICA</a></div>
    </section>
    <section data-reveal class="mt-[80px]"><h2 class="border-b border-[#dedfe0] pb-[14px] text-[32px] leading-[38px] font-medium text-fitinox">Especificaciones técnicas</h2>
        <div class="mt-[20px] grid gap-x-[24px] lg:grid-cols-2">
            @foreach($product->variants->chunk((int) ceil($product->variants->count() / 2)) as $chunk)
            <table class="w-full table-fixed text-[16px] leading-[24px] text-[#35363a]">
                <colgroup><col class="w-[27%]"><col class="w-[22%]"><col class="w-[31%]"><col class="w-[20%]"></colgroup>
                <thead><tr class="h-[50px] border-b border-[#dedfe0] text-left text-[16px] font-semibold text-fitinox"><th class="px-[12px]">Tubo</th><th>Rosca</th><th>Código</th><th></th></tr></thead>
                <tbody>@foreach($chunk as $variant)<tr class="h-[50px] border-b border-[#dedfe0] odd:bg-[#f5f5f5]"><td class="px-[12px]">{{ $variant->tube }}</td><td>{{ $variant->thread }}</td><td class="whitespace-nowrap">{{ $variant->code }}</td><td class="pr-[12px] text-right"><a href="{{ route('quote.index', ['variant' => $variant->id, 'quantity' => 1]) }}" class="inline-flex h-[36px] w-[108px] items-center justify-center rounded-[7px] border border-fitinox px-5 text-[13px] font-medium tracking-[.03em] text-fitinox">CONSULTAR</a></td></tr>@endforeach</tbody>
            </table>
            @endforeach
        </div>
    </section>
    @if($related->isNotEmpty())<section data-reveal class="py-[80px]"><h2 class="text-[32px] font-medium text-fitinox">Productos relacionados</h2><div class="mt-[32px] grid gap-[24px] sm:grid-cols-2 lg:grid-cols-4">@foreach($related as $item)<a href="{{ route('products.show',$item) }}" class="h-[350px] rounded-[6px] border border-[#dedfe0] p-6"><div class="flex h-[220px] items-center justify-center"><img src="{{ Storage::url($item->main_image) }}" class="max-h-full max-w-full object-contain" alt=""></div><p class="text-[12px] font-bold text-[#1954b7]">{{ mb_strtoupper($item->category->name) }}</p><h3 class="mt-2 text-[18px] font-medium">{{ $item->name }}</h3></a>@endforeach</div></section>@endif
</div>

<script>
    document.querySelectorAll('[data-product-media]').forEach((media) => {
        const video = media.querySelector('[data-product-video]');
        const toggle = media.querySelector('[data-video-toggle]');
        if (!video || !toggle) return;

        const playIcon = toggle.querySelector('[data-icon-play]');
        const pauseIcon = toggle.querySelector('[data-icon-pause]');
        let hideTimer;

        const showControl = () => {
            window.clearTimeout(hideTimer);
            toggle.classList.remove('opacity-0');
            if (!video.paused) {
                hideTimer = window.setTimeout(() => toggle.classList.add('opacity-0'), 1200);
            }
        };

        const syncControl = () => {
            const isPaused = video.paused;
            playIcon.classList.toggle('hidden', !isPaused);
            pauseIcon.classList.toggle('hidden', isPaused);
            toggle.setAttribute('aria-label', isPaused ? 'Reproducir video' : 'Pausar video');
            toggle.classList.toggle('opacity-0', !isPaused);
        };

        toggle.addEventListener('click', async () => {
            if (video.paused) {
                await video.play();
            } else {
                video.pause();
            }
            syncControl();
        });

        media.addEventListener('pointermove', showControl);
        media.addEventListener('pointerleave', () => {
            if (!video.paused) toggle.classList.add('opacity-0');
        });
        video.addEventListener('play', syncControl);
        video.addEventListener('pause', syncControl);
        video.addEventListener('ended', syncControl);
        video.play().catch(() => {
            video.muted = true;
            syncControl();
        });
        syncControl();
    });
</script>
@endsection

@extends('layouts.public')

@section('title', $page->seo_title ?: (($settings['seo_title'] ?? null) ?: 'Nosotros | Fitinox'))
@section('meta_description', $page->seo_description ?: (($settings['seo_description'] ?? null) ?: 'Conocé la trayectoria, misión, visión y compromiso con la calidad de Fitinox.'))

@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[300px]">
    <img src="{{ Storage::url($page->hero_image) }}" alt="" class="absolute inset-0 h-full w-full object-cover object-bottom">
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white">
        <p class="pt-5 text-[12px] leading-[1.5] min-[1224px]:pt-[24px]"><a href="/" class="font-bold">Inicio</a> &gt; Nosotros</p>
        <h1 class="absolute bottom-10 left-0 max-w-full text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">{{ $page->hero_title }}</h1>
    </div>
</section>

<section data-reveal class="fitinox-content-1224 grid gap-8 py-12 sm:py-16 min-[1224px]:grid-cols-[601px_minmax(0,1fr)] min-[1224px]:gap-[24px] min-[1224px]:py-[80px]">
    <img src="{{ Storage::url($page->sections['content_image']) }}" alt="{{ $page->sections['content_title'] }}" class="h-[320px] w-full rounded-[8px] object-cover sm:h-[460px] min-[1224px]:!h-[600px] min-[1224px]:w-[601px]">
    <div class="min-[1224px]:pt-[16px]">
        <h2 class="text-[28px] leading-[1.2] font-medium text-fitinox sm:text-[32px]">{{ $page->sections['content_title'] }}</h2>
        <div class="public-rich-text mt-5 max-w-[70ch] text-[16px] leading-[1.6] text-[#35363a] [&_p]:mb-6 [&_p:last-child]:mb-0">{!! $page->content !!}</div>
    </div>
</section>

@php($hasInstitutionalVideo = filled($page->sections['video_cover'] ?? null) && filled($page->sections['video_url'] ?? null))
<section data-reveal class="bg-[#fafaf9] py-12 sm:py-16 min-[1224px]:min-h-[553px] min-[1224px]:pt-[36px] {{ $hasInstitutionalVideo ? 'min-[1224px]:pb-0' : 'min-[1224px]:pb-[80px]' }}">
    <div class="fitinox-content-1224">
        <h2 class="text-[28px] leading-[1.2] font-medium text-fitinox sm:text-[32px]">{{ $page->sections['values_title'] }}</h2>
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            @foreach([
                [$page->sections['mission_title'], $page->sections['mission_text'], $page->sections['mission_icon']],
                [$page->sections['vision_title'], $page->sections['vision_text'], $page->sections['vision_icon']],
                [$page->sections['quality_title'], $page->sections['quality_text'], $page->sections['quality_icon']],
            ] as [$title, $text, $icon])
            <article data-reveal-item class="flex min-h-[320px] min-w-0 flex-col items-center rounded-[6px] bg-white px-5 py-10 text-center min-[1224px]:h-[392px] min-[1224px]:px-[19px] min-[1224px]:pt-[58px] min-[1224px]:pb-5">
                <div class="flex h-16 shrink-0 items-center justify-center"><img src="{{ Storage::url($icon) }}" alt="" class="max-h-16 max-w-16 object-contain"></div>
                <h3 class="mt-7 break-words text-[22px] leading-[1.2] font-semibold text-fitinox sm:text-[24px]">{{ $title }}</h3>
                <p class="mt-5 max-w-[354px] break-words text-[16px] leading-6 text-[#35363a]">{{ $text }}</p>
            </article>
            @endforeach
        </div>
    </div>
</section>

@if($hasInstitutionalVideo)
<section data-reveal class="relative h-[360px] overflow-hidden sm:h-[460px] min-[1224px]:!h-[569px]">
    <img src="{{ Storage::url($page->sections['video_cover']) }}" alt="Video institucional Fitinox" class="absolute inset-0 h-full w-full object-cover">
    <a href="{{ $page->sections['video_url'] }}" target="_blank" rel="noreferrer" aria-label="Reproducir video institucional" class="absolute inset-0 grid place-items-center"><img src="/storage/figma/nosotros/play.svg" alt="" class="size-[60px]"></a>
</section>
@endif
@endsection

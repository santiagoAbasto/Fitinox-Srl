@extends('layouts.public')
@section('title', $page->seo_title ?: 'Videos | Fitinox')
@section('meta_description', $page->seo_description ?: 'Videos institucionales, instructivos y novedades de Fitinox.')
@section('content')
<section class="relative h-[300px] overflow-hidden">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/videos/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-80 mix-blend-multiply"></div>
    <div data-hero-content class="relative mx-auto h-full w-[calc(100%-40px)] max-w-[1224px] text-white xl:w-full">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Videos</p>
        <h1 class="absolute top-[202px] left-0 w-[529px] text-[40px] leading-[48px] font-semibold">{{ $page->hero_title ?: 'Videos' }}</h1>
    </div>
</section>

<section class="mx-auto w-[calc(100%-40px)] max-w-[1224px] py-[80px] lg:h-[587px] lg:py-0 lg:pt-[80px] xl:w-full">
    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3 lg:gap-[24px]">
        @foreach($videos as $video)
            @php($hasMedia = $video->video_path || $video->youtube_url)
            <article data-reveal class="w-full lg:w-[392px]">
                <button type="button"
                    @if($hasMedia) data-video-open data-type="{{ $video->media_type }}" data-source="{{ $video->media_type === 'video' ? Storage::url($video->video_path) : $video->youtube_embed_url }}" @endif
                    class="group relative block h-[256px] w-full overflow-hidden rounded-[8px] bg-[#132b3b] text-left {{ $hasMedia ? 'cursor-pointer' : 'cursor-default' }}"
                    aria-label="{{ $hasMedia ? 'Reproducir '.$video->title : $video->title }}">
                    <img src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.025]">
                    <span class="absolute inset-0 bg-black/20 mix-blend-multiply"></span>
                    <img src="/storage/figma/videos/play.svg" alt="" class="absolute top-[116px] left-1/2 size-6 -translate-x-1/2 transition duration-200 group-hover:scale-110">
                </button>
                <h2 class="mt-[24px] text-[20px] leading-[30px] font-bold text-[#35363a]">{{ $video->title }}</h2>
            </article>
        @endforeach
    </div>
</section>

<div id="video-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-[#07131b]/90 p-5 backdrop-blur-sm" role="dialog" aria-modal="true" aria-label="Reproductor de video">
    <button id="video-close" type="button" class="absolute top-6 right-6 grid size-11 place-items-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20" aria-label="Cerrar">×</button>
    <div id="video-player" class="aspect-video w-full max-w-[1100px] overflow-hidden rounded-[12px] bg-black shadow-2xl"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('video-modal');
    const player = document.getElementById('video-player');
    const close = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); player.innerHTML = ''; document.body.style.overflow = ''; };
    document.querySelectorAll('[data-video-open]').forEach(button => button.addEventListener('click', () => {
        const source = button.dataset.source;
        player.innerHTML = button.dataset.type === 'youtube'
            ? `<iframe src="${source}" class="h-full w-full" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>`
            : `<video src="${source}" class="h-full w-full object-contain" autoplay controls playsinline></video>`;
        modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.style.overflow = 'hidden';
    }));
    document.getElementById('video-close').addEventListener('click', close);
    modal.addEventListener('click', event => { if (event.target === modal) close(); });
    document.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
});
</script>
@endsection

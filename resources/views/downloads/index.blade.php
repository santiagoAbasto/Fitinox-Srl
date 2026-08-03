@extends('layouts.public')
@section('title', $page->seo_title ?: 'Descargas | Fitinox')
@section('content')
<section class="relative h-[300px] overflow-hidden">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/downloads/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-80 mix-blend-multiply"></div>
    <div data-hero-content class="relative mx-auto h-full w-[calc(100%-40px)] max-w-[1224px] text-white xl:w-full">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Descargas</p>
        <h1 class="absolute top-[202px] left-0 w-[529px] text-[40px] leading-[48px] font-semibold">{{ $page->hero_title ?: 'Descargas' }}</h1>
    </div>
</section>

<section data-reveal class="mx-auto w-[calc(100%-40px)] max-w-[1224px] py-[80px] xl:w-full">
    <div class="hidden h-[52px] grid-cols-[149px_450px_158px_131px_169px_167px] items-center rounded-t-[6px] bg-[#132b3b] text-[16px] leading-[19.2px] font-medium text-white lg:grid">
        <span></span><span>Nombre</span><span>Formato</span><span>Peso</span><span></span><span></span>
    </div>
    <div>
        @foreach($downloads as $download)
        <article data-reveal-item class="grid min-h-[84px] items-center gap-3 border-b border-[#d8dcdd] py-5 lg:grid-cols-[149px_450px_158px_131px_169px_167px] lg:gap-0 lg:py-0">
            <div class="flex lg:pl-[28px]"><img src="/storage/figma/downloads/file-text.svg" alt="" class="size-[40px]"></div>
            <div><span class="mb-1 block text-[11px] font-medium text-[#708087] lg:hidden">Nombre</span><h2 class="text-[16px] leading-[24px] font-normal text-[#262424]">{{ $download->title }}</h2></div>
            <div class="text-[16px] leading-[24px] text-[#262424]"><span class="mr-2 font-medium text-[#708087] lg:hidden">Formato:</span>{{ $download->format ?: 'PDF' }}</div>
            <div class="text-[16px] leading-[24px] text-[#262424]"><span class="mr-2 font-medium text-[#708087] lg:hidden">Peso:</span>{{ $download->file_size ?: '—' }}</div>
            <div>
                @if($download->file)
                <a href="{{ route('downloads.view', $download) }}" target="_blank" class="inline-flex h-[36px] w-[145px] items-center justify-center rounded-[7px] border border-[#132b3b] bg-white px-5 text-center text-[13px] leading-[19.5px] font-medium tracking-[.03em] text-[#132b3b]">VER ONLINE</a>
                @else
                <span aria-disabled="true" class="inline-flex h-[36px] w-[145px] cursor-not-allowed items-center justify-center rounded-[7px] border border-[#132b3b] text-[13px] font-medium text-[#132b3b]">VER ONLINE</span>
                @endif
            </div>
            <div>
                @if($download->file)
                <a href="{{ route('downloads.file', $download) }}" class="inline-flex h-[36px] w-[145px] items-center justify-center rounded-[7px] bg-[#132b3b] px-5 text-center text-[13px] leading-[19.5px] font-medium tracking-[.03em] text-white">DESCARGAR</a>
                @else
                <span aria-disabled="true" class="inline-flex h-[36px] w-[145px] cursor-not-allowed items-center justify-center rounded-[7px] bg-[#132b3b] text-[13px] font-medium text-white">DESCARGAR</span>
                @endif
            </div>
        </article>
        @endforeach
    </div>
</section>
@endsection

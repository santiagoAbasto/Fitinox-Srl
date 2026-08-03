@extends('layouts.public')
@section('title', $page->seo_title ?: 'Descargas | Fitinox')
@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[300px]">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/downloads/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-80 mix-blend-multiply"></div>
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Descargas</p>
        <h1 class="absolute bottom-10 left-0 text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">{{ $page->hero_title ?: 'Descargas' }}</h1>
    </div>
</section>

<section data-reveal class="fitinox-content-1224 py-12 sm:py-16 min-[1224px]:py-[80px]">
    @if($downloads->isNotEmpty())
    <div class="hidden h-[52px] grid-cols-[149px_450px_158px_131px_169px_167px] items-center rounded-t-[6px] bg-[#132b3b] text-[16px] leading-[19.2px] font-medium text-white min-[1224px]:grid">
        <span></span><span>Nombre</span><span>Formato</span><span>Peso</span><span></span><span></span>
    </div>
    @endif
    <div>
        @forelse($downloads as $download)
        <article data-reveal-item class="grid min-h-[84px] items-center gap-3 rounded-[8px] border border-[#d8dcdd] p-5 min-[1224px]:grid-cols-[149px_450px_158px_131px_169px_167px] min-[1224px]:gap-0 min-[1224px]:rounded-none min-[1224px]:border-x-0 min-[1224px]:border-t-0 min-[1224px]:p-0">
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
        @empty
        <div class="flex min-h-[220px] flex-col items-center justify-center rounded-[14px] border border-[#dbe3e7] bg-[#f8fafb] px-6 py-12 text-center">
            <span class="grid size-14 place-items-center rounded-full bg-[#eaf1f5]" aria-hidden="true"><img src="/storage/figma/downloads/file-text.svg" alt="" class="size-7 opacity-60"></span>
            <h2 class="mt-5 text-[22px] font-semibold text-[#132b3b]">Aún no existe contenido disponible</h2>
            <p class="mt-2 max-w-[480px] text-[14px] leading-6 text-[#68767d]">Los documentos públicos aparecerán aquí cuando estén disponibles.</p>
        </div>
        @endforelse
    </div>
</section>
@endsection

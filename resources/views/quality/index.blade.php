@extends('layouts.public')
@section('title', $page->seo_title ?: 'Calidad | Fitinox')
@section('content')
@php($sections = $page->sections ?? [])
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[300px]">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/quality/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Calidad</p>
        <h1 class="absolute bottom-10 left-0 text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">{{ $page->hero_title ?: 'Calidad' }}</h1>
    </div>
</section>

<section class="fitinox-content-1224 py-12 sm:py-16 min-[1224px]:pt-[80px] min-[1224px]:pb-[91px]">
    <div class="grid gap-10 md:grid-cols-2 min-[1224px]:h-[600px] min-[1224px]:grid-cols-[584px_601px]">
        <div data-reveal>
            <h2 class="text-[32px] leading-[38.4px] font-medium text-[#132b3b]">{{ $sections['content_title'] ?? 'Políticas de calidad' }}</h2>
            <div class="mt-[10px] text-[16px] leading-[24px] tracking-[-.01em] text-[#35363a]">{!! nl2br(e($page->content)) !!}</div>
            <ul class="mt-[39px] space-y-[7px] text-[14px] leading-[21px] tracking-[-.01em] text-[#35363a]">
                @foreach($sections['policies'] ?? [] as $policy)
                    <li class="flex items-start gap-[7px]"><img src="/storage/figma/quality/check-icon.svg" alt="" class="mt-px size-5 shrink-0"><span>{{ $policy }}</span></li>
                @endforeach
            </ul>
        </div>
        <img data-reveal src="{{ Storage::url($sections['content_image'] ?? 'figma/quality/content.png') }}" alt="Control de calidad Fitinox" class="h-[320px] w-full rounded-[8px] object-cover sm:h-[440px] min-[1224px]:!h-[600px]">
    </div>

    <div data-reveal class="mt-[80px]">
        <h2 class="text-[32px] leading-[38.4px] font-medium text-[#132b3b]">{{ $sections['downloads_title'] ?? 'Descargas' }}</h2>
        <div class="mt-[20px] grid gap-x-[27px] gap-y-[16px] min-[1224px]:grid-cols-2 min-[1224px]:gap-y-[23px]">
            @foreach($documents as $document)
                @if($document->file)
                    <a href="{{ route('quality.documents.download', $document) }}" class="group grid min-h-[84px] grid-cols-[64px_minmax(0,1fr)_44px] items-center gap-3 rounded-[10px] border border-[#dedfe0] bg-[#fbfafa] p-2.5 transition hover:border-[#1954b7] hover:shadow-[0_8px_24px_rgba(19,43,59,.08)] min-[1224px]:h-[60px] min-[1224px]:min-h-0 min-[1224px]:grid-cols-[59px_minmax(0,1fr)_51px] min-[1224px]:gap-0 min-[1224px]:rounded-[4px] min-[1224px]:p-0">
                @else
                    <div class="grid min-h-[84px] grid-cols-[64px_minmax(0,1fr)_44px] items-center gap-3 rounded-[10px] border border-[#dedfe0] bg-[#fbfafa] p-2.5 min-[1224px]:h-[60px] min-[1224px]:min-h-0 min-[1224px]:grid-cols-[59px_minmax(0,1fr)_51px] min-[1224px]:gap-0 min-[1224px]:rounded-[4px] min-[1224px]:p-0">
                @endif
                    <span class="grid size-16 place-items-center overflow-hidden rounded-[7px] bg-white min-[1224px]:h-[58px] min-[1224px]:w-[59px] min-[1224px]:rounded-l-[4px]"><img src="{{ Storage::url($document->logo) }}" alt="" class="max-h-[48px] max-w-[52px] object-contain"></span>
                    <span class="min-w-0 break-words text-[15px] leading-[21px] text-[#35363a] min-[1224px]:truncate min-[1224px]:px-3 min-[1224px]:text-[16px] min-[1224px]:leading-[22px]">{{ $document->title }}</span>
                    <span class="grid size-11 place-items-center rounded-full bg-[#edf3f7] transition group-hover:bg-[#dce9f8]" aria-hidden="true"><img src="/storage/figma/quality/download-icon.svg" alt="" class="size-5 min-[1224px]:size-6"></span>
                @if($document->file)</a>@else</div>@endif
            @endforeach
        </div>
    </div>
</section>
@endsection

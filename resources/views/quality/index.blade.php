@extends('layouts.public')
@section('title', $page->seo_title ?: 'Calidad | Fitinox')
@section('content')
@php($sections = $page->sections ?? [])
<section class="relative h-[300px] overflow-hidden">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/quality/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-80 mix-blend-multiply"></div>
    <div data-hero-content class="relative mx-auto h-full w-[calc(100%-40px)] max-w-[1224px] text-white xl:w-full">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Calidad</p>
        <h1 class="absolute top-[202px] left-0 w-[529px] text-[40px] leading-[48px] font-semibold">{{ $page->hero_title ?: 'Calidad' }}</h1>
    </div>
</section>

<section class="mx-auto w-[calc(100%-40px)] max-w-[1224px] pt-[80px] pb-[91px] xl:w-full">
    <div class="grid gap-10 lg:h-[600px] lg:grid-cols-[584px_601px]">
        <div data-reveal>
            <h2 class="text-[32px] leading-[38.4px] font-medium text-[#132b3b]">{{ $sections['content_title'] ?? 'Políticas de calidad' }}</h2>
            <div class="mt-[10px] text-[16px] leading-[24px] tracking-[-.01em] text-[#35363a]">{!! nl2br(e($page->content)) !!}</div>
            <ul class="mt-[39px] space-y-[7px] text-[14px] leading-[21px] tracking-[-.01em] text-[#35363a]">
                @foreach($sections['policies'] ?? [] as $policy)
                    <li class="flex items-start gap-[7px]"><img src="/storage/figma/quality/check-icon.svg" alt="" class="mt-px size-5 shrink-0"><span>{{ $policy }}</span></li>
                @endforeach
            </ul>
        </div>
        <img data-reveal src="{{ Storage::url($sections['content_image'] ?? 'figma/quality/content.png') }}" alt="Control de calidad Fitinox" class="h-[600px] w-full rounded-[8px] object-cover">
    </div>

    <div data-reveal class="mt-[80px]">
        <h2 class="text-[32px] leading-[38.4px] font-medium text-[#132b3b]">{{ $sections['downloads_title'] ?? 'Descargas' }}</h2>
        <div class="mt-[20px] grid gap-x-[27px] gap-y-[23px] lg:grid-cols-2">
            @foreach($documents as $document)
                @if($document->file)
                    <a href="{{ route('quality.documents.download', $document) }}" class="group flex h-[60px] items-center rounded-[4px] border border-[#dedfe0] bg-[#fbfafa] transition hover:border-[#1954b7] hover:shadow-[0_8px_24px_rgba(19,43,59,.08)]">
                @else
                    <div class="flex h-[60px] items-center rounded-[4px] border border-[#dedfe0] bg-[#fbfafa]">
                @endif
                    <span class="grid h-[58px] w-[59px] shrink-0 place-items-center overflow-hidden rounded-l-[4px] bg-white"><img src="{{ Storage::url($document->logo) }}" alt="" class="max-h-[48px] max-w-[52px] object-contain"></span>
                    <span class="min-w-0 grow truncate px-3 text-[16px] leading-[22px] text-[#35363a]">{{ $document->title }}</span>
                    <img src="/storage/figma/quality/download-icon.svg" alt="Descargar" class="mr-[27px] size-6 shrink-0">
                @if($document->file)</a>@else</div>@endif
            @endforeach
        </div>
    </div>
</section>
@endsection

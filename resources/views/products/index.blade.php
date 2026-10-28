@extends('layouts.public')
@section('title', 'Productos | Fitinox')
@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[350px]">
    <img src="{{ Storage::url($settings['products_hero_image'] ?? 'figma/about.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white"><p class="pt-5 text-[12px] min-[1224px]:pt-[24px]"><a href="/" class="font-bold">Inicio</a> &gt; Productos</p><h1 class="absolute bottom-10 left-0 text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">Productos</h1></div>
</section>
<section data-reveal class="fitinox-content-1224 py-12 sm:py-16 min-[1224px]:pt-[80px] min-[1224px]:pb-[284px]">
    <div class="grid gap-[24px] sm:grid-cols-2 lg:grid-cols-4">
        @foreach($categories as $category)
            <x-category-card :category="$category" data-reveal-item />
        @endforeach
    </div>
</section>
@endsection

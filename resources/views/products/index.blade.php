@extends('layouts.public')
@section('title', 'Productos | Fitinox')
@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[350px]">
    <img src="/storage/figma/about.png" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-90 mix-blend-multiply"></div>
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white"><p class="pt-5 text-[12px] min-[1224px]:pt-[24px]"><a href="/" class="font-bold">Inicio</a> &gt; Productos</p><h1 class="absolute bottom-10 left-0 text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">Productos</h1></div>
</section>
<section data-reveal class="fitinox-content-1224 py-12 sm:py-16 min-[1224px]:pt-[80px] min-[1224px]:pb-[284px]">
    <div class="grid gap-[24px] sm:grid-cols-2 lg:grid-cols-4">
        @foreach($categories as $category)
        <a data-reveal-item href="{{ route('products.category', $category) }}" class="group relative h-[300px] overflow-hidden rounded-[6px] bg-[#f5f5f5] sm:h-[350px] min-[1224px]:!h-[392px]">
            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-white/0 to-black/75"></div>
            <div class="absolute right-[18px] bottom-[29px] left-[18px] flex items-end justify-between text-white"><strong class="max-w-[220px] text-[20px] leading-[30px]">{{ $category->name }}</strong><span class="text-[30px] font-light">+</span></div>
        </a>
        @endforeach
    </div>
</section>
@endsection

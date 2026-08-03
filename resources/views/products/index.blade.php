@extends('layouts.public')
@section('title', 'Productos | Fitinox')
@section('content')
<section class="relative h-[350px] overflow-hidden">
    <img src="/storage/figma/about.png" alt="" class="absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] opacity-90 mix-blend-multiply"></div>
    <div data-hero-content class="relative mx-auto h-full max-w-[1224px] text-white"><p class="pt-[24px] text-[12px]"><a href="/" class="font-bold">Inicio</a> &gt; Productos</p><h1 class="absolute top-[202px] left-0 w-[529px] text-[40px] leading-[48px] font-semibold">Productos</h1></div>
</section>
<section data-reveal class="mx-auto w-[calc(100%-40px)] max-w-[1224px] pt-[80px] pb-[80px] xl:w-full xl:pb-[284px]">
    <div class="grid gap-[24px] sm:grid-cols-2 lg:grid-cols-4">
        @foreach($categories as $category)
        <a data-reveal-item href="{{ route('products.category', $category) }}" class="group relative h-[392px] overflow-hidden rounded-[6px] bg-[#f5f5f5]">
            <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-white/0 to-black/75"></div>
            <div class="absolute right-[18px] bottom-[29px] left-[18px] flex items-end justify-between text-white"><strong class="max-w-[220px] text-[20px] leading-[30px]">{{ $category->name }}</strong><span class="text-[30px] font-light">+</span></div>
        </a>
        @endforeach
    </div>
</section>
@endsection

@extends('layouts.public')

@section('title', $page->seo_title ?: ($settings['seo_title'] ?? 'Fitinox | Componentes para sistemas de control de fluidos'))
@section('meta_description', $page->seo_description ?: (($settings['seo_description'] ?? null) ?: 'Componentes y soluciones Fitinox para conducción, instrumentación y control de fluidos.'))

@section('content')
    <section id="home-slider" class="home-hero relative overflow-hidden bg-fitinox">
        @forelse($slides as $index => $slide)
            <article data-home-slide class="absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'z-10 opacity-100' : 'pointer-events-none z-0 opacity-0' }}">
                @if($slide->media_type === 'video' && $slide->media_path)
                    <video class="absolute inset-0 h-full w-full object-cover object-center" autoplay muted loop playsinline preload="{{ $index === 0 ? 'auto' : 'metadata' }}"><source src="{{ Storage::url($slide->media_path) }}"></video>
                @elseif($slide->media_type === 'youtube' && $slide->youtube_embed_url)
                    <iframe class="pointer-events-none absolute top-1/2 left-1/2 h-[56.25vw] min-h-full w-[177.78vh] min-w-full -translate-x-1/2 -translate-y-1/2" src="{{ $slide->youtube_embed_url }}" title="{{ $slide->title }}" allow="autoplay; encrypted-media" tabindex="-1"></iframe>
                @else
                    <img src="{{ Storage::url($slide->media_path ?: $page->hero_image ?: 'figma/about.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
                @endif
                <div class="absolute inset-0 bg-fitinox/65"></div>
                <div class="relative fitinox-content-1224 h-full">
                    <div data-hero-content class="absolute inset-x-0 bottom-[72px] w-full max-w-[565px] text-white sm:bottom-[88px] min-[1224px]:top-[218px] min-[1224px]:bottom-auto">
                        <h1 class="font-sans text-[32px] leading-[1.15] font-semibold sm:text-[38px] md:text-[44px] min-[1224px]:text-[48px] min-[1224px]:leading-[1.2]">{{ $slide->title }}</h1>
                        @if($slide->button_label)
                            <a href="{{ $slide->button_url ?: '#productos' }}" class="mt-7 inline-flex min-h-11 items-center justify-center gap-[10px] rounded-[7px] bg-fitinox px-5 py-2 text-[13px] font-medium tracking-[.03em] min-[1224px]:mt-[45px]">{{ $slide->button_label }}</a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ Storage::url($page->hero_image ?: 'figma/about.png') }}')"></div>
            <div class="absolute inset-0 bg-fitinox/65"></div>
            <div class="relative fitinox-content-1224 h-full"><div data-hero-content class="absolute inset-x-0 bottom-[72px] w-full max-w-[565px] text-white sm:bottom-[88px] min-[1224px]:top-[218px] min-[1224px]:bottom-auto"><h1 class="text-[32px] leading-[1.15] font-semibold sm:text-[38px] md:text-[44px] min-[1224px]:text-[48px] min-[1224px]:leading-[1.2]">{{ $page->hero_title }}</h1></div></div>
        @endforelse
        @if($slides->count() > 1)
            <div class="absolute bottom-3 left-1/2 z-20 flex -translate-x-1/2 sm:bottom-5">
                @foreach($slides as $index => $slide)<button type="button" data-slide-dot="{{ $index }}" aria-label="Mostrar slide {{ $index + 1 }}" class="grid size-11 place-items-center"><span data-slide-dot-indicator class="block h-1.5 rounded-full bg-white transition-all {{ $index === 0 ? 'w-8' : 'w-3 opacity-50' }}"></span></button>@endforeach
            </div>
        @endif
    </section>

    <section id="productos" data-reveal class="fitinox-content-1224 py-12 sm:py-16 xl:py-20">
        <div class="flex items-center justify-between gap-3 sm:gap-5"><h2 class="text-[30px] leading-[1.2] font-semibold text-fitinox sm:text-[34px] md:text-[36px] xl:text-[40px]">{{ $page->sections['products_title'] ?? 'Productos' }}</h2><a href="{{ $page->sections['products_button_url'] ?? route('products.index') }}" class="inline-flex min-h-11 shrink-0 items-center rounded-[7px] border border-fitinox px-4 py-2 text-[13px] font-medium text-fitinox sm:px-5">{{ $page->sections['products_button_label'] ?? 'VER TODOS' }}</a></div>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $category)
            <a href="{{ route('products.category', $category) }}" data-reveal-item class="group relative h-[300px] overflow-hidden rounded-[6px] sm:h-[340px] lg:h-[392px]">
                <img src="{{ Storage::url($category->image ?: 'products/product-placeholder.svg') }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/75"></div>
                <div class="absolute right-5 bottom-6 left-5 flex items-end justify-between gap-3 text-white"><strong class="text-[20px] leading-[1.5]">{{ $category->name }}</strong><span class="text-3xl font-light">+</span></div>
            </a>
            @endforeach
        </div>
    </section>

    <section data-reveal class="grid overflow-hidden bg-fitinox lg:grid-cols-2 xl:min-h-[600px]">
        <div class="min-h-[280px] bg-cover bg-center sm:min-h-[360px] lg:min-h-[520px] xl:min-h-[600px]" style="background-image: url('{{ Storage::url($page->sections['about_image'] ?? 'figma/about.png') }}')"></div>
        <div class="flex items-center px-4 py-12 text-white sm:px-8 sm:py-16 xl:px-[51px]"><div class="max-w-[570px]"><h2 class="text-[30px] leading-[1.2] font-semibold sm:text-[34px] md:text-[36px] xl:text-[40px]">{{ $page->sections['about_title'] }}</h2><div class="public-rich-text mt-6 text-[15px] leading-6 sm:mt-7 sm:text-base [&_a]:underline [&_h2]:text-2xl [&_h3]:text-xl [&_ol]:mb-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-5 [&_p:last-child]:mb-0 [&_ul]:mb-5 [&_ul]:list-disc [&_ul]:pl-6">{!! str_contains($page->content, '<') ? $page->content : nl2br(e($page->content)) !!}</div><a href="{{ $page->sections['about_button_url'] ?? route('about') }}" class="mt-8 inline-flex min-h-11 items-center rounded-[7px] border border-fitinox bg-white px-5 py-2 text-[13px] font-medium text-fitinox sm:mt-10">{{ $page->sections['about_button_label'] ?? 'MAS INFO' }}</a></div></div>
    </section>

    <section data-reveal class="fitinox-content-1224 py-12 sm:py-16 xl:py-20">
        <h2 class="text-[30px] leading-[1.2] font-semibold text-fitinox sm:text-[34px] md:text-[36px] xl:text-[40px]">{{ $page->sections['featured_title'] ?? 'Productos destacados' }}</h2>
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($products as $product)
            <a href="{{ route('products.show', $product) }}" data-reveal-item class="block h-[350px] overflow-hidden rounded-[6px] border border-fitinox-line bg-white">
                <div class="flex h-[230px] items-center justify-center p-5"><img src="{{ Storage::url($product->main_image) }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain"></div>
                <div class="px-6"><p class="text-[12px] font-bold text-fitinox-accent">{{ mb_strtoupper($product->category->name) }}</p><h3 class="mt-2 text-[18px] font-medium tracking-[-.01em] text-black">{{ $product->name }}</h3></div>
            </a>
            @endforeach
        </div>
    </section>

    @if($page->sections['industry_is_active'] ?? true)
    <section data-reveal class="relative flex min-h-[420px] items-center justify-center overflow-hidden bg-cover bg-center py-16 text-center text-white sm:min-h-[381px] xl:h-[381px] xl:min-h-0 xl:py-0" style="background-image: url('{{ Storage::url($page->sections['industry_image'] ?? 'figma/oil-gas.png') }}')">
        <div class="absolute inset-0 bg-gradient-to-t from-fitinox/80 to-[#3475a1]/60"></div>
        <div class="relative max-w-[720px] px-4 sm:px-5"><h2 class="text-[30px] leading-[1.2] font-semibold sm:text-[34px] md:text-[36px] xl:text-[40px]">{{ $page->sections['industry_title'] }}</h2><div class="mt-3 space-y-2 text-base leading-[1.5] sm:text-[18px] xl:text-[20px] [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-6">{!! str_contains($page->sections['industry_text'], '<') ? $page->sections['industry_text'] : nl2br(e($page->sections['industry_text'])) !!}</div><a href="{{ $page->sections['industry_button_url'] ?? '#' }}" class="mt-6 inline-flex min-h-11 items-center rounded-[7px] bg-white px-5 py-2 text-[13px] font-medium text-fitinox">{{ $page->sections['industry_button_label'] ?? 'MAS INFO' }}</a></div>
    </section>
    @endif
@endsection

@push('scripts')
@if($slides->count() > 1)
<script>
(() => {
    const slides = [...document.querySelectorAll('[data-home-slide]')];
    const dots = [...document.querySelectorAll('[data-slide-dot]')];
    const indicators = dots.map((dot) => dot.querySelector('[data-slide-dot-indicator]'));
    let active = 0;
    const show = (next) => {
        slides[active].classList.add('opacity-0', 'pointer-events-none', 'z-0');
        slides[active].classList.remove('opacity-100', 'z-10');
        indicators[active].classList.remove('w-8');
        indicators[active].classList.add('w-3', 'opacity-50');
        active = next;
        slides[active].classList.remove('opacity-0', 'pointer-events-none', 'z-0');
        slides[active].classList.add('opacity-100', 'z-10');
        indicators[active].classList.add('w-8');
        indicators[active].classList.remove('w-3', 'opacity-50');
    };
    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.setInterval(() => show((active + 1) % slides.length), 6500);
    }
})();
</script>
@endif
@endpush

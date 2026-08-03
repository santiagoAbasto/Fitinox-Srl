<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->seo_title ?: ($settings['seo_title'] ?? 'Fitinox | Componentes para sistemas de control de fluidos') }}</title>
    <meta name="description" content="{{ $page->seo_description ?: ($settings['seo_description'] ?? '') }}">
    @php($pageKeywords = $page->sections['seo_keywords'] ?? null)
    @if(!empty($pageKeywords ?: ($settings['seo_keywords'] ?? null)))<meta name="keywords" content="{{ $pageKeywords ?: $settings['seo_keywords'] }}">@endif
    <meta property="og:title" content="{{ $page->seo_title ?: ($settings['seo_title'] ?? 'Fitinox') }}">
    <meta property="og:description" content="{{ $page->seo_description ?: ($settings['seo_description'] ?? '') }}">
    @include('components.share-image-meta', ['settings' => $settings, 'fallbackImage' => $page->sections['header_logo'] ?? null, 'pageImage' => $page->sections['seo_og_image'] ?? null])
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/public-motion.js'])
</head>
<body>
<header data-public-header class="sticky top-0 z-50 h-[100px] border-b border-black/5 bg-white shadow-[0_4px_4px_rgba(0,0,0,.08)]">
    <div class="relative fitinox-content-1224 flex h-full items-center">
        <a href="/" class="shrink-0 xl:absolute xl:top-[26px] xl:left-0"><img src="{{ Storage::url($page->sections['header_logo'] ?? 'figma/header-footer/header-logo.png') }}" alt="Fitinox" class="h-[47px] w-[259.532px] max-w-[60vw] object-cover"></a>
        <nav class="hidden items-center gap-[14px] text-[14px] leading-[1.5] font-normal text-[#35363a] xl:absolute xl:top-[38px] xl:left-[302px] xl:flex">
            @foreach([
                [$page->sections['nav_about'] ?? 'Nosotros', '/nosotros'],
                [$page->sections['nav_products'] ?? 'Productos', '/productos'],
                [$page->sections['nav_downloads'] ?? 'Descargas', '/descargas'],
                [$page->sections['nav_quality'] ?? 'Calidad', '/calidad'],
                [$page->sections['nav_videos'] ?? 'Videos', '/videos'],
                [$page->sections['nav_distributors'] ?? 'Distribuidores', '/distribuidores'],
                [$page->sections['nav_quote'] ?? 'Presupuesto', '/presupuesto'],
                [$page->sections['nav_contact'] ?? 'Contacto', '/contacto']
            ] as [$item, $href])<a href="{{ $href }}" class="hover:text-fitinox">{{ $item }}</a>@endforeach
        </nav>
        <div class="pointer-events-none ml-auto flex items-center gap-4 text-[14px] text-fitinox xl:absolute xl:inset-0 xl:ml-0 xl:block">
            <button type="button" data-catalog-search-trigger aria-label="Buscar productos" class="pointer-events-auto hidden cursor-pointer sm:block xl:absolute xl:top-[37px] xl:left-[986px]"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-6"></button>
            <img src="/storage/figma/header-footer/header-divider.svg" alt="" class="hidden h-[21px] w-px sm:block xl:absolute xl:top-[39.5px] xl:left-[1026px]">
            @include('components.locale-menu', ['class' => 'pointer-events-auto hidden leading-[1.5] sm:flex xl:absolute xl:top-[38px] xl:left-[1043px]'])
            <button type="button" data-client-login-trigger class="pointer-events-auto flex h-[36px] w-[120px] cursor-pointer items-center justify-center gap-[10px] whitespace-nowrap rounded-[7px] border border-[#132b3b] bg-white px-5 py-2 text-center text-[14px] leading-[1.5] font-normal text-[#35363a] transition hover:bg-[#edf3f7] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] xl:absolute xl:top-[31px] xl:left-[1104px]"><img src="/storage/figma/header-footer/users.svg" alt="" class="h-6 w-6 shrink-0">{{ app()->getLocale() === 'en' ? 'CLIENTS' : ($page->sections['client_label'] ?? 'CLIENTES') }}</button>
        </div>
    </div>
</header>
@include('components.catalog-search')
@include('components.client-login-modal')

<main>
    <section id="home-slider" class="relative h-[668px] overflow-hidden bg-fitinox">
        @forelse($slides as $index => $slide)
            <article data-home-slide class="absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'z-10 opacity-100' : 'pointer-events-none z-0 opacity-0' }}">
                @if($slide->media_type === 'video' && $slide->media_path)
                    <video class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline preload="{{ $index === 0 ? 'auto' : 'metadata' }}"><source src="{{ Storage::url($slide->media_path) }}"></video>
                @elseif($slide->media_type === 'youtube' && $slide->youtube_embed_url)
                    <iframe class="pointer-events-none absolute top-1/2 left-1/2 h-[56.25vw] min-h-full w-[177.78vh] min-w-full -translate-x-1/2 -translate-y-1/2" src="{{ $slide->youtube_embed_url }}" title="{{ $slide->title }}" allow="autoplay; encrypted-media" tabindex="-1"></iframe>
                @else
                    <img src="{{ Storage::url($slide->media_path ?: $page->hero_image ?: 'figma/about.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                @endif
                <div class="absolute inset-0 bg-fitinox/65"></div>
                <div class="relative fitinox-content-1224 h-full">
                    <div data-hero-content class="absolute top-[218px] left-0 w-[565px] max-w-full text-white">
                        <h1 class="font-sans text-[48px] leading-[1.2] font-semibold">{{ $slide->title }}</h1>
                        @if($slide->button_label)
                            <a href="{{ $slide->button_url ?: '#productos' }}" class="mt-[45px] inline-flex items-center justify-center gap-[10px] rounded-[7px] bg-fitinox px-5 py-2 text-[13px] font-medium tracking-[.03em]">{{ $slide->button_label }}</a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ Storage::url($page->hero_image ?: 'figma/about.png') }}')"></div>
            <div class="absolute inset-0 bg-fitinox/65"></div>
            <div class="relative fitinox-content-1224 h-full"><div data-hero-content class="absolute top-[218px] left-0 w-[565px] max-w-full text-white"><h1 class="text-[48px] leading-[1.2] font-semibold">{{ $page->hero_title }}</h1></div></div>
        @endforelse
        @if($slides->count() > 1)
            <div class="absolute bottom-8 left-1/2 z-20 flex -translate-x-1/2 gap-2">
                @foreach($slides as $index => $slide)<button type="button" data-slide-dot="{{ $index }}" aria-label="Mostrar slide {{ $index + 1 }}" class="h-1.5 rounded-full bg-white transition-all {{ $index === 0 ? 'w-8' : 'w-3 opacity-50' }}"></button>@endforeach
            </div>
        @endif
    </section>

    <section id="productos" data-reveal class="fitinox-content-1224 py-20">
        <div class="flex items-center justify-between gap-5"><h2 class="text-[40px] leading-[1.2] font-semibold text-fitinox">{{ $page->sections['products_title'] ?? 'Productos' }}</h2><a href="{{ $page->sections['products_button_url'] ?? route('products.index') }}" class="shrink-0 rounded-[7px] border border-fitinox px-5 py-2 text-[13px] font-medium text-fitinox">{{ $page->sections['products_button_label'] ?? 'VER TODOS' }}</a></div>
        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($categories as $category)
            <a href="{{ route('products.category', $category) }}" data-reveal-item class="group relative h-[392px] overflow-hidden rounded-[6px]">
                <img src="{{ Storage::url($category->image ?: 'products/product-placeholder.svg') }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/75"></div>
                <div class="absolute right-5 bottom-6 left-5 flex items-end justify-between gap-3 text-white"><strong class="text-[20px] leading-[1.5]">{{ $category->name }}</strong><span class="text-3xl font-light">+</span></div>
            </a>
            @endforeach
        </div>
    </section>

    <section data-reveal class="grid min-h-[600px] overflow-hidden bg-fitinox lg:grid-cols-2">
        <div class="min-h-[440px] bg-cover bg-center" style="background-image: url('{{ Storage::url($page->sections['about_image'] ?? 'figma/about.png') }}')"></div>
        <div class="flex items-center px-8 py-16 text-white lg:px-[51px]"><div class="max-w-[570px]"><h2 class="text-[40px] leading-[1.2] font-semibold">{{ $page->sections['about_title'] }}</h2><div class="public-rich-text mt-7 text-base leading-6 [&_a]:underline [&_h2]:text-2xl [&_h3]:text-xl [&_ol]:mb-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-5 [&_p:last-child]:mb-0 [&_ul]:mb-5 [&_ul]:list-disc [&_ul]:pl-6">{!! str_contains($page->content, '<') ? $page->content : nl2br(e($page->content)) !!}</div><a href="{{ $page->sections['about_button_url'] ?? route('about') }}" class="mt-10 inline-flex rounded-[7px] border border-fitinox bg-white px-5 py-2 text-[13px] font-medium text-fitinox">{{ $page->sections['about_button_label'] ?? 'MAS INFO' }}</a></div></div>
    </section>

    <section data-reveal class="fitinox-content-1224 py-20">
        <h2 class="text-[40px] leading-[1.2] font-semibold text-fitinox">{{ $page->sections['featured_title'] ?? 'Productos destacados' }}</h2>
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
    <section data-reveal class="relative flex h-[381px] items-center justify-center overflow-hidden bg-cover bg-center text-center text-white" style="background-image: url('{{ Storage::url($page->sections['industry_image'] ?? 'figma/oil-gas.png') }}')">
        <div class="absolute inset-0 bg-gradient-to-t from-fitinox/80 to-[#3475a1]/60"></div>
        <div class="relative max-w-[720px] px-5"><h2 class="text-[40px] leading-[1.2] font-semibold">{{ $page->sections['industry_title'] }}</h2><div class="mt-3 space-y-2 text-[20px] leading-[1.5] [&_a]:underline [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-6">{!! str_contains($page->sections['industry_text'], '<') ? $page->sections['industry_text'] : nl2br(e($page->sections['industry_text'])) !!}</div><a href="{{ $page->sections['industry_button_url'] ?? '#' }}" class="mt-6 inline-flex rounded-[7px] bg-white px-5 py-2 text-[13px] font-medium text-fitinox">{{ $page->sections['industry_button_label'] ?? 'MAS INFO' }}</a></div>
    </section>
    @endif
</main>

<footer id="footer" data-reveal class="relative overflow-hidden bg-fitinox text-white lg:h-[415px]">
    <img src="/storage/figma/header-footer/footer-background.svg" alt="" class="pointer-events-none absolute inset-0 z-0 h-full w-full object-fill">
    <div class="relative z-10 fitinox-content-1224 grid gap-10 py-12 md:grid-cols-2 lg:h-[348px] lg:grid-cols-[262px_363px_310px_289px] lg:gap-0 lg:py-0">
        <div class="lg:pt-[51px]">
            <a href="{{ route('home') }}" aria-label="Ir al inicio"><img src="/storage/figma/header-footer/footer-logo-mask.png" class="mt-[3px] h-[41px] w-[225px] object-contain brightness-0 invert" alt="Fitinox"></a>
        </div>

        <div class="lg:pt-[45px]">
            <h3 class="text-[20px] leading-[1.2] font-semibold">Secciones</h3>
            <div class="mt-[25px] grid w-[288px] grid-cols-[168px_120px] text-[16px] leading-[2.3]">
                <div><a class="block" href="/nosotros">{{ $page->sections['nav_about'] ?? 'Nosotros' }}</a><a class="block" href="/productos">{{ $page->sections['nav_products'] ?? 'Productos' }}</a><a class="block" href="/descargas">{{ $page->sections['nav_downloads'] ?? 'Descargas' }}</a><a class="block" href="/calidad">{{ $page->sections['nav_quality'] ?? 'Calidad' }}</a></div>
                <div><a class="block" href="/videos">{{ $page->sections['nav_videos'] ?? 'Videos' }}</a><a class="block" href="/distribuidores">{{ $page->sections['nav_distributors'] ?? 'Distribuidores' }}</a><a class="block" href="/presupuesto">{{ $page->sections['nav_quote'] ?? 'Presupuesto' }}</a><a class="block" href="/contacto">{{ $page->sections['nav_contact'] ?? 'Contacto' }}</a></div>
            </div>
        </div>

        <div class="lg:pt-[45px]">
            <h3 class="text-[20px] leading-[1.2] font-semibold">{{ $page->sections['newsletter_title'] ?? 'Suscribite al Newsletter' }}</h3>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-[26px] flex h-[40px] w-[289px] items-center rounded-[4px] border border-[#dedfe0] px-3 text-[14px]">
                @csrf
                <input type="email" name="email" required aria-label="Email" placeholder="Email" class="min-w-0 grow bg-transparent outline-none placeholder:text-white">
                <button type="submit" aria-label="Suscribirme" class="grid size-5 shrink-0 place-items-center"><img src="/storage/figma/header-footer/arrow-right.svg" alt="" class="size-5"></button>
            </form>
            <h3 class="mt-[25px] text-[20px] leading-[1.2] font-semibold">{{ $page->sections['social_title'] ?? 'Seguinos en' }}</h3>
            @php($socialLinks = ['instagram' => $settings['instagram_url'] ?? '', 'facebook' => $settings['facebook_url'] ?? '', 'linkedin' => $settings['linkedin_url'] ?? ''])
            <div class="mt-[20px] flex gap-3">
                @foreach($socialLinks as $social => $url)
                    @if($url)<a href="{{ $url }}" target="_blank" rel="noreferrer" aria-label="{{ ucfirst($social) }}"><img src="/storage/figma/header-footer/{{ $social }}.svg" alt="" class="size-5"></a>@else<span title="Configurá {{ ucfirst($social) }} desde Administración" class="opacity-55"><img src="/storage/figma/header-footer/{{ $social }}.svg" alt="" class="size-5"></span>@endif
                @endforeach
            </div>
        </div>

        <div class="relative lg:pt-[45px]">
            <h3 class="text-[20px] leading-[1.2] font-semibold">Contacto</h3>
            @php($address = $settings['address'] ?? 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina')
            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address) }}" target="_blank" rel="noreferrer" class="absolute top-[90px] left-0 flex h-[69px] w-[288px] items-start gap-[11px] text-[16px] leading-[24px] font-normal text-white">
                <img src="/storage/figma/header-footer/map-pin.svg" alt="" class="mt-[3px] size-5 shrink-0">
                <span class="min-w-0 flex-1">{{ $address }}</span>
            </a>

            <img src="/storage/figma/header-footer/mail.svg" alt="" class="absolute top-[171px] left-0 size-5">
            <div class="absolute top-[167px] left-[31px] w-[258px] text-[16px] leading-[1.5]">
                @foreach([
                    $settings['email_sales'] ?? 'ventas@fittinox.com.ar',
                    $settings['email_quality'] ?? 'calidad@fittinox.com.ar',
                    $settings['email_warehouse'] ?? 'deposito@fittinox.com.ar',
                    $settings['email_admin'] ?? 'administracion@fittinox.com.ar'
                ] as $email)<a class="block" href="mailto:{{ $email }}">{{ $email }}</a>@endforeach
            </div>

            <img src="/storage/figma/header-footer/phone.svg" alt="" class="absolute top-[273px] left-0 size-5">
            <div class="absolute top-[270px] left-[31px] w-[258px] text-[16px] leading-[24px] font-normal">
                <a class="block" href="tel:+541147385562">+54 (011) 4738-5562 /</a>
                <a class="block" href="tel:+541147642389">+54 (011) 4764-2389 / 6647/8</a>
            </div>

            <img src="/storage/figma/header-footer/whatsapp.svg" alt="" class="absolute top-[326px] left-0 size-5">
            <a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '5491140732016' }}" target="_blank" rel="noreferrer" class="absolute top-[323px] left-[31px] w-[258px] text-[16px] leading-[24px] font-normal">{{ $settings['whatsapp'] ?? '54 9 11 4073-2016' }}</a>
        </div>
    </div>
    <div class="relative z-10 h-[67px]">
        <div class="pointer-events-none absolute inset-0 z-0 opacity-10" style="background: linear-gradient(0deg, rgba(0, 0, 0, 0.40) 0%, rgba(0, 0, 0, 0.40) 100%), #152168;"></div>
        <div class="relative z-10 fitinox-content-1224 flex h-full items-center justify-between text-[14px] leading-[1.5]">
            <span>{{ $settings['copyright'] ?? '© Copyright 2025 Fitinox S.R.L. Todos los derechos reservados' }}</span>
            <span>By <a data-no-translate href="https://osole.com.ar/" target="_blank" rel="noreferrer" class="font-bold">Osole</a></span>
        </div>
    </div>
</footer>
@if(($settings['whatsapp_enabled'] ?? '1') === '1')
<a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '5491140732016' }}?text={{ rawurlencode($settings['whatsapp_message'] ?? 'Hola Fitinox, quisiera realizar una consulta.') }}" target="_blank" aria-label="Consultar por WhatsApp" class="fixed {{ ($settings['whatsapp_position'] ?? 'right') === 'left' ? 'left-[35px]' : 'right-[35px]' }} bottom-[35px] z-40 grid size-[64px] place-items-center rounded-full bg-[#19bd4b] shadow-[0_12px_35px_rgba(25,189,75,.35)]"><img src="/storage/figma/header-footer/whatsapp.svg" class="size-8 brightness-0 invert" alt=""></a>
@endif
@if($slides->count() > 1)
<script>
(() => {
    const slides = [...document.querySelectorAll('[data-home-slide]')];
    const dots = [...document.querySelectorAll('[data-slide-dot]')];
    let active = 0;
    const show = (next) => {
        slides[active].classList.add('opacity-0', 'pointer-events-none', 'z-0');
        slides[active].classList.remove('opacity-100', 'z-10');
        dots[active].classList.remove('w-8');
        dots[active].classList.add('w-3', 'opacity-50');
        active = next;
        slides[active].classList.remove('opacity-0', 'pointer-events-none', 'z-0');
        slides[active].classList.add('opacity-100', 'z-10');
        dots[active].classList.add('w-8');
        dots[active].classList.remove('w-3', 'opacity-50');
    };
    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.setInterval(() => show((active + 1) % slides.length), 6500);
    }
})();
</script>
@endif
</body></html>

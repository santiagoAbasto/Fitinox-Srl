<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="analytics-enabled" content="{{ config('analytics.enabled') ? '1' : '0' }}">
    <meta name="posthog-key" content="{{ config('analytics.posthog.key') }}"><meta name="posthog-host" content="{{ config('analytics.posthog.host') }}"><meta name="posthog-recordings" content="{{ config('analytics.posthog.recordings') ? '1' : '0' }}">
    <title>@yield('title', $settings['seo_title'] ?? 'Productos | Fitinox')</title>
    <meta name="description" content="@yield('meta_description', $settings['seo_description'] ?? 'Componentes y soluciones Fitinox para conducción, instrumentación y control de fluidos.')">
    @php($pageKeywords = data_get($page ?? null, 'sections.seo_keywords'))
    @if(!empty($pageKeywords ?: ($settings['seo_keywords'] ?? null)))<meta name="keywords" content="{{ $pageKeywords ?: $settings['seo_keywords'] }}">@endif
    <meta property="og:title" content="@yield('title', $settings['seo_title'] ?? 'Fitinox')">
    <meta property="og:description" content="@yield('meta_description', $settings['seo_description'] ?? '')">
    @include('components.share-image-meta', ['settings' => $settings, 'fallbackImage' => data_get($page ?? null, 'sections.header_logo'), 'pageImage' => data_get($page ?? null, 'sections.seo_og_image')])
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/public-motion.js'])
    @if(config('services.turnstile.site_key'))<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
</head>
<body>
@php($downloadsEnabled = ($settings['downloads_enabled'] ?? '1') !== '0')
@php($clientAreaEnabled = ($settings['client_area_enabled'] ?? '1') !== '0')
@php($publicNav = collect([
    [data_get($page ?? null, 'sections.nav_about', 'Nosotros'), '/nosotros', 'about'],
    [data_get($page ?? null, 'sections.nav_products', 'Productos'), '/productos', 'products.*'],
    $downloadsEnabled ? [data_get($page ?? null, 'sections.nav_downloads', 'Descargas'), '/descargas', 'downloads.*'] : null,
    [data_get($page ?? null, 'sections.nav_quality', 'Calidad'), '/calidad', 'quality.*'],
    [data_get($page ?? null, 'sections.nav_videos', 'Videos'), '/videos', 'videos.*'],
    [data_get($page ?? null, 'sections.nav_distributors', 'Distribuidores'), '/distribuidores', 'distributors.*'],
    [data_get($page ?? null, 'sections.nav_quote', 'Presupuesto'), '/presupuesto', 'quote.*'],
    [data_get($page ?? null, 'sections.nav_contact', 'Contacto'), '/contacto', 'contact.*'],
])->filter()->values()->all())
<div data-mobile-nav-root>
<header data-public-header class="sticky top-0 z-50 h-[80px] bg-white shadow-[0_4px_4px_rgba(0,0,0,.08)] min-[1224px]:h-[100px]">
    <div class="fitinox-shell relative flex h-full items-center">
        <a href="/" class="shrink-0 min-[1224px]:absolute min-[1224px]:top-[26px] min-[1224px]:left-0"><img src="{{ Storage::url(data_get($page ?? null, 'sections.header_logo', 'figma/header-footer/header-logo.png')) }}" alt="Fitinox" class="h-auto w-[180px] object-contain sm:w-[220px] min-[1224px]:h-[47px] min-[1224px]:w-[263px]"></a>
        <nav aria-label="{{ app()->getLocale() === 'en' ? 'Main navigation' : 'Navegación principal' }}" class="hidden items-center gap-[14px] text-[14px] leading-[1.5] text-[#35363a] min-[1224px]:absolute min-[1224px]:top-[38px] min-[1224px]:left-[302px] min-[1224px]:flex">
            @foreach($publicNav as [$label, $href, $route])<a href="{{ $href }}" class="{{ request()->routeIs($route) ? 'font-bold' : '' }}" @if(request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>@endforeach
        </nav>
        <div class="pointer-events-none ml-auto flex items-center gap-1 sm:gap-2 min-[1224px]:absolute min-[1224px]:inset-0 min-[1224px]:ml-0 min-[1224px]:block">
            <button type="button" data-catalog-search-trigger aria-label="Buscar productos" class="mobile-nav-trigger pointer-events-auto grid cursor-pointer place-items-center min-[1224px]:absolute min-[1224px]:top-[28px] min-[1224px]:left-[976px]"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-6"></button>
            <button type="button" data-mobile-nav-trigger aria-controls="mobile-public-navigation" aria-expanded="false" aria-label="{{ app()->getLocale() === 'en' ? 'Open menu' : 'Abrir menú' }}" class="mobile-nav-trigger pointer-events-auto grid cursor-pointer place-items-center min-[1224px]:hidden"><span aria-hidden="true" class="space-y-[5px]"><span class="block h-0.5 w-6 bg-current"></span><span class="block h-0.5 w-6 bg-current"></span><span class="block h-0.5 w-6 bg-current"></span></span></button>
            <img src="/storage/figma/header-footer/header-divider.svg" alt="" class="hidden h-[21px] w-px min-[1224px]:absolute min-[1224px]:top-[39.5px] min-[1224px]:left-[1026px] min-[1224px]:block">
            @include('components.locale-menu', ['class' => 'pointer-events-auto hidden text-[14px] text-fitinox min-[1224px]:absolute min-[1224px]:top-[38px] min-[1224px]:left-[1043px] min-[1224px]:flex'])
            @if($clientAreaEnabled)<button type="button" data-client-login-trigger class="pointer-events-auto hidden h-[36px] w-[120px] cursor-pointer items-center justify-center gap-[10px] whitespace-nowrap rounded-[7px] border border-[#132b3b] bg-white px-5 py-2 text-[14px] leading-[21px] text-[#35363a] transition hover:bg-[#edf3f7] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] min-[1224px]:absolute min-[1224px]:top-[31px] min-[1224px]:left-[1104px] min-[1224px]:flex"><img src="/storage/figma/header-footer/users.svg" alt="" class="size-6 shrink-0">{{ app()->getLocale() === 'en' ? 'CLIENTS' : data_get($page ?? null, 'sections.client_label', 'CLIENTES') }}</button>@endif
        </div>
    </div>
</header>
@if(session('newsletter_notice'))
    @php($newsletterNotice = session('newsletter_notice'))
    <div data-newsletter-toast role="status" aria-live="polite" aria-atomic="true" class="public-toast fixed top-[96px] right-4 z-[90] w-[min(400px,calc(100vw-32px))] overflow-hidden rounded-[18px] border border-white/80 bg-white/95 shadow-[0_24px_70px_rgba(7,31,46,.22)] backdrop-blur-xl min-[1224px]:top-[120px] min-[1224px]:right-6">
        <div class="flex items-start gap-3 p-4 sm:p-5">
            <span class="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#e8f8f0] text-[#17845c]" aria-hidden="true"><svg class="size-6" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
            <div class="min-w-0 grow pt-0.5"><p class="text-[13px] font-semibold text-[#132b3b]">{{ $newsletterNotice['title'] }}</p><p class="mt-1 text-[11px] leading-[17px] text-[#65747c] sm:text-[12px]">{{ $newsletterNotice['message'] }}</p></div>
            <button type="button" data-newsletter-toast-close class="grid size-8 shrink-0 place-items-center rounded-full text-xl leading-none text-[#8b979d] transition hover:bg-[#edf3f7] hover:text-[#132b3b]" aria-label="Cerrar notificación">×</button>
        </div>
        <div class="h-[3px] bg-[#e8edef]"><div class="public-toast-progress h-full origin-left bg-gradient-to-r from-[#34c98b] to-[#1954b7]"></div></div>
    </div>
    <script>
    (() => {
        const toast = document.querySelector('[data-newsletter-toast]');
        if (!toast) return;
        const close = () => { toast.classList.add('is-leaving'); window.setTimeout(() => toast.remove(), 180); };
        toast.querySelector('[data-newsletter-toast-close]')?.addEventListener('click', close);
        window.setTimeout(close, 6500);
    })();
    </script>
@endif
<button type="button" data-mobile-nav-overlay aria-label="{{ app()->getLocale() === 'en' ? 'Close menu' : 'Cerrar menú' }}" class="mobile-nav-overlay" tabindex="-1"></button>
<aside id="mobile-public-navigation" data-mobile-nav class="mobile-nav-panel" aria-hidden="true" aria-label="{{ app()->getLocale() === 'en' ? 'Main navigation' : 'Navegación principal' }}">
    <div class="flex items-center justify-between border-b border-[#dedfe0] pb-5">
        <a href="/" aria-label="Ir al inicio"><img src="{{ Storage::url(data_get($page ?? null, 'sections.header_logo', 'figma/header-footer/header-logo.png')) }}" alt="Fitinox" class="h-auto w-[190px] object-contain"></a>
        <button type="button" data-mobile-nav-close aria-label="{{ app()->getLocale() === 'en' ? 'Close menu' : 'Cerrar menú' }}" class="mobile-nav-trigger grid place-items-center text-[32px] font-light leading-none">&times;</button>
    </div>
    <nav class="mt-5 flex flex-col" aria-label="Navegación móvil">
        @foreach($publicNav as [$label, $href, $route])<a href="{{ $href }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs($route) ? 'font-semibold text-[#1954b7]' : 'text-[#35363a]' }}" @if(request()->routeIs($route)) aria-current="page" @endif>{{ $label }}</a>@endforeach
    </nav>
    <div class="mt-6 grid gap-3">
        <button type="button" data-catalog-search-trigger class="flex min-h-11 items-center gap-3 rounded-[7px] border border-[#dedfe0] px-4 text-[14px] text-[#132b3b]"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-5">{{ app()->getLocale() === 'en' ? 'Search products' : 'Buscar productos' }}</button>
        @include('components.locale-menu', ['class' => 'flex min-h-11 items-center rounded-[7px] border border-[#dedfe0] px-4 text-[14px] text-fitinox'])
        @if($clientAreaEnabled)<button type="button" data-client-login-trigger class="flex min-h-11 items-center justify-center gap-3 rounded-[7px] bg-fitinox px-4 text-[14px] font-medium text-white"><img src="/storage/figma/header-footer/users.svg" alt="" class="size-5 brightness-0 invert">{{ app()->getLocale() === 'en' ? 'CLIENTS' : data_get($page ?? null, 'sections.client_label', 'CLIENTES') }}</button>@endif
    </div>
</aside>
</div>
@include('components.catalog-search')
@if($clientAreaEnabled)@include('components.client-login-modal')@endif
<main>@yield('content')</main>
<footer id="footer" data-reveal class="relative overflow-hidden bg-fitinox text-white xl:h-[415px]">
    <img src="/storage/figma/header-footer/footer-background.svg" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-fill">
    <div class="fitinox-shell relative grid gap-10 py-12 md:grid-cols-2 xl:h-[348px] xl:grid-cols-[262px_363px_310px_289px] xl:gap-0 xl:py-0">
        <div class="xl:pt-[51px]"><img src="/storage/figma/header-footer/footer-logo-mask.png" class="mt-[3px] h-[41px] w-[225px] object-contain brightness-0 invert" alt="Fitinox"></div>
        <div class="xl:pt-[45px]"><h3 class="text-[20px] font-semibold">Secciones</h3><div class="mt-[25px] grid w-[288px] grid-cols-[168px_120px] text-[16px] leading-[2.3]"><div><a class="block" href="/nosotros">Nosotros</a><a class="block" href="/productos">Productos</a>@if($downloadsEnabled)<a class="block" href="/descargas">Descargas</a>@endif<a class="block" href="/calidad">Calidad</a></div><div><a class="block" href="/videos">Videos</a><a class="block" href="/distribuidores">Distribuidores</a><a class="block" href="/presupuesto">Presupuesto</a><a class="block" href="/contacto">Contacto</a></div></div></div>
        <div class="xl:pt-[45px]">
            <h3 class="text-[20px] font-semibold">Suscribite al Newsletter</h3>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-[26px] w-full max-w-[289px]">@csrf<div class="flex h-[44px] items-center rounded-[4px] border {{ $errors->newsletter->has('email') ? 'border-[#ff9a9a]' : 'border-[#dedfe0]' }} px-3 text-[14px]"><input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" aria-label="Email" @if($errors->newsletter->has('email')) aria-invalid="true" aria-describedby="newsletter-email-error" @endif placeholder="Email" class="min-w-0 grow bg-transparent outline-none placeholder:text-white"><button type="submit" aria-label="Suscribirme" class="grid size-11 shrink-0 place-items-center"><img src="/storage/figma/header-footer/arrow-right.svg" alt="" class="size-5"></button></div>@error('email', 'newsletter')<p id="newsletter-email-error" class="mt-2 text-[11px] leading-4 text-[#ffd2d2]">Ingresá un correo electrónico válido.</p>@enderror</form>
            @php($socialLinks = collect(['instagram' => $settings['instagram_url'] ?? null, 'facebook' => $settings['facebook_url'] ?? null, 'linkedin' => $settings['linkedin_url'] ?? null])->map(fn ($url) => is_string($url) ? trim($url) : '')->filter()->all())
            @if(count($socialLinks))
                <h3 class="mt-[25px] text-[20px] font-semibold">Seguinos en</h3>
                <div class="mt-5 flex gap-3">
                    @foreach($socialLinks as $social => $url)
                        <a href="{{ $url }}" target="_blank" rel="noreferrer" aria-label="{{ ucfirst($social) }}"><img src="/storage/figma/header-footer/{{ $social }}.svg" alt="" class="size-5"></a>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="xl:pt-[45px]">
            <h3 class="text-[20px] font-semibold">Contacto</h3>
            @php($address = $settings['address'] ?? 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina')
            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address) }}" target="_blank" rel="noreferrer" class="mt-6 flex gap-[11px] text-[16px] leading-[24px] xl:mt-[22px] xl:h-[69px] xl:w-[288px]"><img src="/storage/figma/header-footer/map-pin.svg" class="mt-1 size-5 shrink-0" alt=""><span>{{ $address }}</span></a>
            <div class="mt-3 flex min-w-0 gap-[11px] xl:mt-2"><img src="/storage/figma/header-footer/mail.svg" class="mt-1 size-5 shrink-0" alt=""><div class="min-w-0 text-[16px] leading-[24px]">@foreach([$settings['email_sales'] ?? 'ventas@fittinox.com.ar',$settings['email_quality'] ?? 'calidad@fittinox.com.ar',$settings['email_warehouse'] ?? 'deposito@fittinox.com.ar',$settings['email_admin'] ?? 'administracion@fittinox.com.ar'] as $email)<a href="mailto:{{ $email }}" class="block [overflow-wrap:anywhere]">{{ $email }}</a>@endforeach</div></div>
            <div class="mt-3 flex gap-[11px] xl:mt-1.5"><img src="/storage/figma/header-footer/phone.svg" class="mt-1 size-5 shrink-0" alt=""><div class="text-[16px] leading-[24px]"><a href="tel:+541147385562" class="block">+54 (011) 4738-5562 /</a><a href="tel:+541147642389" class="block">+54 (011) 4764-2389 / 6647/8</a></div></div>
            @php($whatsappNumber = $settings['whatsapp_number'] ?? '5491140732016')
            <div class="mt-3 flex gap-[11px] xl:mt-1.5"><img src="/storage/figma/header-footer/whatsapp.svg" class="mt-0.5 size-5 shrink-0" alt=""><a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noreferrer" class="text-[16px] leading-[24px]">{{ $settings['whatsapp'] ?? '54 9 11 4073-2016' }}</a></div>
        </div>
    </div>
    <div data-footer-copyright class="relative min-h-[67px]"><div class="absolute inset-0 opacity-10" style="background:linear-gradient(0deg,rgba(0,0,0,.4),rgba(0,0,0,.4)),#152168"></div><div class="fitinox-shell relative flex min-h-[67px] flex-col items-start justify-center gap-1 py-3 text-[13px] sm:text-[14px] xl:flex-row xl:items-center xl:justify-between xl:py-0"><span>{{ $settings['copyright'] ?? '© Copyright 2025 Fitinox S.R.L. Todos los derechos reservados' }}</span><span>By <a data-no-translate href="https://osole.com.ar/" target="_blank" rel="noreferrer" class="font-bold">Osole</a></span></div></div>
</footer>
@if(($settings['whatsapp_enabled'] ?? '1') === '1')
<a data-floating-whatsapp href="https://wa.me/{{ $settings['whatsapp_number'] ?? '5491140732016' }}?text={{ rawurlencode($settings['whatsapp_message'] ?? 'Hola Fitinox, quisiera realizar una consulta.') }}" target="_blank" aria-label="Consultar por WhatsApp" class="floating-whatsapp-pulse fixed {{ ($settings['whatsapp_position'] ?? 'right') === 'left' ? 'left-4 xl:left-[35px]' : 'right-4 xl:right-[35px]' }} bottom-4 z-40 grid size-14 place-items-center rounded-full bg-[#19bd4b] shadow-[0_12px_35px_rgba(25,189,75,.35)] will-change-transform xl:bottom-[35px] xl:size-[64px]"><img src="/storage/figma/header-footer/whatsapp.svg" class="size-7 brightness-0 invert xl:size-8" alt=""></a>
@endif
@stack('scripts')
</body>
</html>

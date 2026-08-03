<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="analytics-enabled" content="{{ config('analytics.enabled') ? '1' : '0' }}">
    <meta name="posthog-key" content="{{ config('analytics.posthog.key') }}"><meta name="posthog-host" content="{{ config('analytics.posthog.host') }}"><meta name="posthog-recordings" content="{{ config('analytics.posthog.recordings') ? '1' : '0' }}">
    <title>@yield('title', $settings['seo_title'] ?? 'Productos | Fitinox')</title>
    <meta name="description" content="@yield('meta_description', $settings['seo_description'] ?? 'Componentes y soluciones Fitinox para conducción, instrumentación y control de fluidos.')">
    @php($pageKeywords = data_get($page ?? null, 'sections.seo_keywords'))
    @if(!empty($pageKeywords ?: ($settings['seo_keywords'] ?? null)))<meta name="keywords" content="{{ $pageKeywords ?: $settings['seo_keywords'] }}">@endif
    <meta property="og:title" content="@yield('title', $settings['seo_title'] ?? 'Fitinox')">
    <meta property="og:description" content="@yield('meta_description', $settings['seo_description'] ?? '')">
    @include('components.share-image-meta', ['settings' => $settings, 'pageImage' => data_get($page ?? null, 'sections.seo_og_image')])
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/public-motion.js'])
    @if(config('services.turnstile.site_key'))<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
</head>
<body>
<header data-public-header class="sticky top-0 z-50 h-[100px] bg-white shadow-[0_4px_4px_rgba(0,0,0,.08)]">
    <div class="relative mx-auto flex h-full w-[calc(100%-40px)] max-w-[1224px] items-center xl:w-full">
        <a href="/" class="shrink-0 xl:absolute xl:top-[26px] xl:left-0"><img src="/storage/figma/header-footer/header-logo.png" alt="Fitinox" class="h-[47px] w-[263px] max-w-[60vw] object-cover"></a>
        <nav class="hidden items-center gap-[14px] text-[14px] leading-[1.5] text-[#35363a] xl:absolute xl:top-[38px] xl:left-[302px] xl:flex">
            <a href="/nosotros" class="{{ request()->routeIs('about') ? 'font-bold' : '' }}">Nosotros</a><a href="/productos" class="{{ request()->routeIs('products.*') ? 'font-bold' : '' }}">Productos</a><a href="/descargas" class="{{ request()->routeIs('downloads.*') ? 'font-bold' : '' }}">Descargas</a><a href="/calidad" class="{{ request()->routeIs('quality.*') ? 'font-bold' : '' }}">Calidad</a><a href="/videos" class="{{ request()->routeIs('videos.*') ? 'font-bold' : '' }}">Videos</a><a href="/distribuidores" class="{{ request()->routeIs('distributors.*') ? 'font-bold' : '' }}">Distribuidores</a><a href="/presupuesto" class="{{ request()->routeIs('quote.*') ? 'font-bold' : '' }}">Presupuesto</a><a href="/contacto" class="{{ request()->routeIs('contact.*') ? 'font-bold' : '' }}">Contacto</a>
        </nav>
        <div class="pointer-events-none ml-auto flex items-center gap-4 xl:absolute xl:inset-0 xl:ml-0 xl:block">
            <button type="button" data-catalog-search-trigger aria-label="Buscar productos" class="pointer-events-auto hidden cursor-pointer xl:absolute xl:top-[37px] xl:left-[986px] xl:block"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-6"></button>
            <img src="/storage/figma/header-footer/header-divider.svg" alt="" class="hidden h-[21px] w-px xl:absolute xl:top-[39.5px] xl:left-[1026px] xl:block">
            @include('components.locale-menu', ['class' => 'pointer-events-auto hidden text-[14px] text-fitinox xl:absolute xl:top-[38px] xl:left-[1043px] xl:flex'])
            <button type="button" data-client-login-trigger class="pointer-events-auto flex h-[36px] w-[120px] cursor-pointer items-center justify-center gap-[10px] whitespace-nowrap rounded-[7px] border border-[#132b3b] bg-white px-5 py-2 text-[14px] leading-[21px] text-[#35363a] transition hover:bg-[#edf3f7] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] xl:absolute xl:top-[31px] xl:left-[1104px]"><img src="/storage/figma/header-footer/users.svg" alt="" class="size-6 shrink-0">{{ app()->getLocale() === 'en' ? 'CLIENTS' : 'CLIENTES' }}</button>
        </div>
    </div>
</header>
@include('components.catalog-search')
@include('components.client-login-modal')
<main>@yield('content')</main>
<footer id="footer" data-reveal class="relative overflow-hidden bg-fitinox text-white lg:h-[415px]">
    <img src="/storage/figma/header-footer/footer-background.svg" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-fill">
    <div class="relative mx-auto grid w-[calc(100%-40px)] max-w-[1224px] gap-10 py-12 md:grid-cols-2 lg:h-[348px] lg:grid-cols-[262px_363px_310px_289px] lg:gap-0 lg:py-0 xl:w-full">
        <div class="lg:pt-[51px]"><img src="/storage/figma/header-footer/footer-logo-mask.png" class="mt-[3px] h-[41px] w-[225px] object-contain brightness-0 invert" alt="Fitinox"></div>
        <div class="lg:pt-[45px]"><h3 class="text-[20px] font-semibold">Secciones</h3><div class="mt-[25px] grid w-[288px] grid-cols-[168px_120px] text-[16px] leading-[2.3]"><div><a class="block" href="/nosotros">Nosotros</a><a class="block" href="/productos">Productos</a><a class="block" href="/descargas">Descargas</a><a class="block" href="/calidad">Calidad</a></div><div><a class="block" href="/videos">Videos</a><a class="block" href="/distribuidores">Distribuidores</a><a class="block" href="/presupuesto">Presupuesto</a><a class="block" href="/contacto">Contacto</a></div></div></div>
        <div class="lg:pt-[45px]">
            <h3 class="text-[20px] font-semibold">Suscribite al Newsletter</h3>
            <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-[26px] flex h-[40px] w-[289px] items-center rounded-[4px] border border-[#dedfe0] px-3 text-[14px]">@csrf<input type="email" name="email" required placeholder="Email" class="grow bg-transparent outline-none placeholder:text-white"><button type="submit" aria-label="Suscribirme"><img src="/storage/figma/header-footer/arrow-right.svg" alt="" class="size-5"></button></form>
            <h3 class="mt-[25px] text-[20px] font-semibold">Seguinos en</h3>
            @php($socialLinks = ['instagram' => $settings['instagram_url'] ?? '', 'facebook' => $settings['facebook_url'] ?? '', 'linkedin' => $settings['linkedin_url'] ?? ''])
            <div class="mt-5 flex gap-3">
                @foreach($socialLinks as $social => $url)
                    @if($url)
                        <a href="{{ $url }}" target="_blank" rel="noreferrer" aria-label="{{ ucfirst($social) }}"><img src="/storage/figma/header-footer/{{ $social }}.svg" alt="" class="size-5"></a>
                    @else
                        <span title="Configurá {{ ucfirst($social) }} desde Administración" class="opacity-55"><img src="/storage/figma/header-footer/{{ $social }}.svg" alt="" class="size-5"></span>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="relative lg:pt-[45px]">
            <h3 class="text-[20px] font-semibold">Contacto</h3>
            @php($address = $settings['address'] ?? 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina')
            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address) }}" target="_blank" rel="noreferrer" class="absolute top-[90px] left-0 flex h-[69px] w-[288px] gap-[11px] text-[16px] leading-[24px]"><img src="/storage/figma/header-footer/map-pin.svg" class="mt-1 size-5 shrink-0" alt=""><span>{{ $address }}</span></a>
            <img src="/storage/figma/header-footer/mail.svg" class="absolute top-[171px] left-0 size-5" alt=""><div class="absolute top-[167px] left-[31px] text-[16px] leading-[24px]">@foreach([$settings['email_sales'] ?? 'ventas@fittinox.com.ar',$settings['email_quality'] ?? 'calidad@fittinox.com.ar',$settings['email_warehouse'] ?? 'deposito@fittinox.com.ar',$settings['email_admin'] ?? 'administracion@fittinox.com.ar'] as $email)<a href="mailto:{{ $email }}" class="block">{{ $email }}</a>@endforeach</div>
            <img src="/storage/figma/header-footer/phone.svg" class="absolute top-[273px] left-0 size-5" alt=""><div class="absolute top-[270px] left-[31px] text-[16px] leading-[24px]"><a href="tel:+541147385562" class="block">+54 (011) 4738-5562 /</a><a href="tel:+541147642389" class="block">+54 (011) 4764-2389 / 6647/8</a></div>
            @php($whatsappNumber = $settings['whatsapp_number'] ?? '5491140732016')
            <img src="/storage/figma/header-footer/whatsapp.svg" class="absolute top-[326px] left-0 size-5" alt=""><a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="absolute top-[323px] left-[31px] text-[16px] leading-[24px]">{{ $settings['whatsapp'] ?? '54 9 11 4073-2016' }}</a>
        </div>
    </div>
    <div class="relative h-[67px]"><div class="absolute inset-0 opacity-10" style="background:linear-gradient(0deg,rgba(0,0,0,.4),rgba(0,0,0,.4)),#152168"></div><div class="relative mx-auto flex h-full max-w-[1224px] items-center justify-between text-[14px]"><span>{{ $settings['copyright'] ?? '© Copyright 2025 Fitinox S.R.L. Todos los derechos reservados' }}</span><span>By <a data-no-translate href="https://osole.com.ar/" target="_blank" rel="noreferrer" class="font-bold">Osole</a></span></div></div>
</footer>
@if(($settings['whatsapp_enabled'] ?? '1') === '1')
<a href="https://wa.me/{{ $settings['whatsapp_number'] ?? '5491140732016' }}?text={{ rawurlencode($settings['whatsapp_message'] ?? 'Hola Fitinox, quisiera realizar una consulta.') }}" target="_blank" aria-label="Consultar por WhatsApp" class="fixed {{ ($settings['whatsapp_position'] ?? 'right') === 'left' ? 'left-[35px]' : 'right-[35px]' }} bottom-[35px] z-40 grid size-[64px] place-items-center rounded-full bg-[#19bd4b] shadow-[0_12px_35px_rgba(25,189,75,.35)]"><img src="/storage/figma/header-footer/whatsapp.svg" class="size-8 brightness-0 invert" alt=""></a>
@endif
</body>
</html>

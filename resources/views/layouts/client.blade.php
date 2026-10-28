<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'Zona Clientes | Fitinox')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/public-motion.js'])
</head>
<body class="client-area min-h-screen bg-white font-[Montserrat] text-[#35363a]">
    @php($settings = $settings ?? \App\Models\Setting::pluck('value', 'key'))
    <div data-mobile-nav-root>
    <header data-public-header class="sticky top-0 z-50 h-[80px] bg-white shadow-[0_4px_4px_rgba(0,0,0,.08)] min-[1224px]:h-[100px]">
        <div class="relative fitinox-content-1224 h-full">
            <button type="button" data-private-public-link class="absolute top-[19px] left-0 cursor-pointer min-[1224px]:top-[26px]" aria-label="Ir al sitio público"><img src="/storage/figma/header-footer/header-logo.png" alt="Fitinox" class="h-auto w-[180px] object-contain sm:w-[220px] min-[1224px]:h-[47px] min-[1224px]:w-[259.532px]"></button>
            <div class="client-header-actions {{ app()->getLocale() === 'en' ? 'client-header-actions--en' : '' }} hidden min-[1224px]:absolute min-[1224px]:top-[32px] min-[1224px]:left-[525.502px] min-[1224px]:flex min-[1224px]:h-[36px] min-[1224px]:items-center">
                <nav aria-label="Zona Clientes" class="flex items-center gap-[14px] whitespace-nowrap text-center text-[14px] leading-[21px] text-[#35363a]">
                    <a href="{{ route('client.products') }}" class="transition hover:text-[#1954b7] {{ request()->routeIs('client.products') ? 'font-bold' : 'font-normal' }}">Productos</a>
                    <a href="{{ route('client.cart') }}" class="transition hover:text-[#1954b7] {{ request()->routeIs('client.cart') ? 'font-bold' : 'font-normal' }}">Carrito</a>
                    <a href="{{ route('client.price-list') }}" class="transition hover:text-[#1954b7] {{ request()->routeIs('client.price-list*') ? 'font-bold' : 'font-normal' }}">Lista de precios</a>
                    <a href="{{ route('client.orders') }}" class="transition hover:text-[#1954b7] {{ request()->routeIs('client.orders*') ? 'font-bold' : 'font-normal' }}">Histórico de pedidos</a>
                </nav>
                <button type="button" data-catalog-search-trigger aria-label="Buscar productos" class="ml-[15.5px] grid size-6 cursor-pointer place-items-center transition hover:opacity-65"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-6"></button>
                <img src="/storage/figma/header-footer/header-divider.svg" alt="" class="ml-[24px] h-[21px] w-px">
                @include('components.locale-menu', ['class' => 'ml-[23px] inline-flex text-center text-[16px] font-normal leading-[24px] text-[#132b3b]'])
                <button type="button" data-client-menu-trigger aria-expanded="false" aria-controls="client-account-menu" class="ml-[34px] inline-flex h-[36px] w-[120px] items-center justify-center gap-[10px] rounded-[7px] bg-[#132b3b] px-[20px] py-[8px] text-[14px] font-normal leading-[21px] text-white transition hover:bg-[#1954b7]" title="{{ $client->name }}"><img src="/storage/figma/header-footer/users.svg" alt="" class="size-6 shrink-0 brightness-0 invert"><span class="truncate">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::limit($client->name, 6, '')) }}</span></button>
            </div>
            <button type="button" data-mobile-nav-trigger aria-controls="mobile-client-navigation" aria-expanded="false" aria-label="Abrir menú" class="mobile-nav-trigger absolute top-[18px] right-0 grid place-items-center min-[1224px]:hidden"><span aria-hidden="true" class="space-y-[5px]"><span class="block h-0.5 w-6 bg-current"></span><span class="block h-0.5 w-6 bg-current"></span><span class="block h-0.5 w-6 bg-current"></span></span></button>
            <div id="client-account-menu" class="absolute top-[76px] right-0 z-[70] hidden w-[248px] overflow-hidden rounded-[16px] border border-[#e2e8eb] bg-white p-2 shadow-[0_18px_45px_rgba(7,19,27,.18)]">
                <div class="border-b border-[#edf0f2] px-3 py-3"><span class="block truncate text-[13px] font-semibold text-[#132b3b]">{{ $client->name }}</span><span class="mt-0.5 block truncate text-[11px] text-[#758189]">{{ $client->email }}</span></div>
                <form method="POST" action="{{ route('client.logout') }}" class="pt-2">@csrf<button class="flex h-10 w-full items-center gap-2 rounded-[10px] px-3 text-left text-[13px] font-semibold text-[#c44b56] transition hover:bg-[#fff1f2]"><svg class="size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M10 17l5-5-5-5m5 5H3m18 7V5a2 2 0 0 0-2-2h-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>Cerrar sesión</button></form>
            </div>
        </div>
    </header>
    <button type="button" data-mobile-nav-overlay aria-label="Cerrar menú" class="mobile-nav-overlay" tabindex="-1"></button>
    <aside id="mobile-client-navigation" data-mobile-nav class="mobile-nav-panel" aria-hidden="true" aria-label="Zona Clientes">
        <div class="flex items-center justify-between border-b border-[#dedfe0] pb-5"><button type="button" data-private-public-link aria-label="Ir al sitio público"><img src="/storage/figma/header-footer/header-logo.png" alt="Fitinox" class="h-auto w-[190px] object-contain"></button><button type="button" data-mobile-nav-close aria-label="Cerrar menú" class="mobile-nav-trigger grid place-items-center text-[32px] font-light leading-none">&times;</button></div>
        <div class="mt-5 border-b border-[#e7ecef] pb-4"><span class="block truncate text-[14px] font-semibold text-[#132b3b]">{{ $client->name }}</span><span class="mt-1 block truncate text-[12px] text-[#758189]">{{ $client->email }}</span></div>
        <nav class="mt-3 flex flex-col" aria-label="Navegación de clientes">
            <a href="{{ route('client.dashboard') }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs('client.dashboard') ? 'font-semibold text-[#1954b7]' : '' }}">Inicio</a>
            <a href="{{ route('client.products') }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs('client.products') ? 'font-semibold text-[#1954b7]' : '' }}">Productos</a>
            <a href="{{ route('client.cart') }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs('client.cart') ? 'font-semibold text-[#1954b7]' : '' }}">Carrito</a>
            <a href="{{ route('client.price-list') }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs('client.price-list*') ? 'font-semibold text-[#1954b7]' : '' }}">Lista de precios</a>
            <a href="{{ route('client.orders') }}" class="flex min-h-11 items-center border-b border-[#e7ecef] py-3 text-[15px] {{ request()->routeIs('client.orders*') ? 'font-semibold text-[#1954b7]' : '' }}">Histórico de pedidos</a>
        </nav>
        <div class="mt-6 grid gap-3"><button type="button" data-catalog-search-trigger class="flex min-h-11 items-center gap-3 rounded-[7px] border border-[#dedfe0] px-4 text-[14px]"><img src="/storage/figma/header-footer/search.svg" alt="" class="size-5">Buscar productos</button>@include('components.locale-menu', ['class' => 'flex min-h-11 items-center rounded-[7px] border border-[#dedfe0] px-4 text-[14px] text-fitinox'])<form method="POST" action="{{ route('client.logout') }}">@csrf<button class="flex min-h-11 w-full items-center justify-center rounded-[7px] bg-[#132b3b] px-4 text-[14px] font-medium text-white">Cerrar sesión</button></form></div>
    </aside>
    </div>
    @include('components.catalog-search', ['privateSearch' => true])
    @if(session('success'))<div role="status" aria-live="polite" class="fixed top-[116px] right-5 z-[60] w-[min(390px,calc(100vw-40px))] rounded-[18px] border border-white/70 bg-white/95 p-4 shadow-[0_20px_65px_rgba(7,19,27,.18)] backdrop-blur"><div class="flex items-start gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-[13px] bg-[#e9f8f0] text-[#17845c]"><svg class="size-5" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><p class="pt-1 text-[13px] leading-5 text-[#334851]">{{ session('success') }}</p></div></div>@endif
    <main>@yield('content')</main>
    <footer id="footer" class="relative mt-0 overflow-hidden bg-fitinox text-white lg:h-[415px]">
        <img src="/storage/figma/header-footer/footer-background.svg" alt="" class="pointer-events-none absolute inset-0 h-full w-full object-fill">
        <div class="relative fitinox-content-1224 grid gap-10 py-12 md:grid-cols-2 lg:h-[348px] lg:grid-cols-[262px_363px_310px_289px] lg:gap-0 lg:py-0">
            <div class="lg:pt-[51px]"><button type="button" data-private-public-link class="cursor-pointer" aria-label="Ir al sitio público"><img src="/storage/figma/header-footer/footer-logo-mask.png" class="mt-[3px] h-[41px] w-[225px] object-contain brightness-0 invert" alt="Fitinox"></button></div>
            <div class="lg:pt-[45px]"><h3 class="text-[20px] font-semibold">Zona Clientes</h3><div class="mt-[25px] grid w-[288px] grid-cols-[168px_120px] text-[16px] leading-[2.3]"><div><a class="block" href="{{ route('client.products') }}">Productos</a><a class="block" href="{{ route('client.cart') }}">Carrito</a></div><div><a class="block" href="{{ route('client.price-list') }}">Lista de precios</a><a class="block" href="{{ route('client.orders') }}">Histórico de pedidos</a></div></div></div>
            <div class="lg:pt-[45px]">
                <h3 class="text-[20px] font-semibold">Suscribite al Newsletter</h3>
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mt-[26px] flex h-[40px] w-[289px] items-center rounded-[4px] border border-[#dedfe0] px-3 text-[14px]">@csrf<input type="email" name="email" required placeholder="Email" aria-label="Email" class="grow bg-transparent outline-none placeholder:text-white"><button type="submit" aria-label="Suscribirme"><img src="/storage/figma/header-footer/arrow-right.svg" alt="" class="size-5"></button></form>
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
            <div class="relative lg:pt-[45px]">
                <h3 class="text-[20px] font-semibold">Contacto</h3>
                @php($address = $settings['address'] ?? 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina')
                <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address) }}" target="_blank" rel="noreferrer" class="absolute top-[90px] left-0 flex h-[69px] w-[288px] gap-[11px] text-[16px] leading-[24px]"><img src="/storage/figma/header-footer/map-pin.svg" class="mt-1 size-5 shrink-0" alt=""><span>{{ $address }}</span></a>
                <img src="/storage/figma/header-footer/mail.svg" class="absolute top-[171px] left-0 size-5" alt=""><div class="absolute top-[167px] left-[31px] text-[16px] leading-[24px]">@foreach([$settings['email_sales'] ?? 'ventas@fittinox.com.ar',$settings['email_quality'] ?? 'calidad@fittinox.com.ar',$settings['email_warehouse'] ?? 'deposito@fittinox.com.ar',$settings['email_admin'] ?? 'administracion@fittinox.com.ar'] as $email)<a href="mailto:{{ $email }}" class="block">{{ $email }}</a>@endforeach</div>
                <img src="/storage/figma/header-footer/phone.svg" class="absolute top-[273px] left-0 size-5" alt=""><div class="absolute top-[270px] left-[31px] text-[16px] leading-[24px]"><a href="tel:+541147385562" class="block">+54 (011) 4738-5562 /</a><a href="tel:+541147642389" class="block">+54 (011) 4764-2389 / 6647/8</a></div>
                <img src="/storage/figma/header-footer/whatsapp.svg" class="absolute top-[326px] left-0 size-5" alt=""><a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016') }}" target="_blank" rel="noreferrer" class="absolute top-[323px] left-[31px] text-[16px] leading-[24px]">{{ preg_match('/^549\d{10}$/', preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016')) ? '+54 9 '.substr(preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016'), 3, 2).' '.substr(preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016'), 5, 4).'-'.substr(preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016'), 9) : '+'.preg_replace('/\D/', '', $settings['whatsapp_number'] ?? '5491140732016') }}</a>
            </div>
        </div>
        <div data-footer-copyright class="relative h-[67px]"><div class="absolute inset-0 opacity-10" style="background:linear-gradient(0deg,rgba(0,0,0,.4),rgba(0,0,0,.4)),#152168"></div><div class="relative fitinox-content-1224 flex h-full items-center justify-between text-[14px]"><span>{{ str_replace('2025', '2026', $settings['copyright'] ?? '© Copyright 2026 Fitinox S.R.L. Todos los derechos reservados') }}</span><span>By <a data-no-translate href="https://osole.com.ar/" target="_blank" rel="noreferrer" class="font-bold">Osole</a></span></div></div>
    </footer>
    @if(($settings['whatsapp_enabled'] ?? '1') === '1')
        <a data-floating-whatsapp href="https://wa.me/{{ $settings['whatsapp_number'] ?? '5491140732016' }}?text={{ rawurlencode($settings['whatsapp_message'] ?? 'Hola Fitinox, quisiera realizar una consulta.') }}" target="_blank" rel="noreferrer" aria-label="Consultar por WhatsApp" class="floating-whatsapp-pulse fixed {{ ($settings['whatsapp_position'] ?? 'right') === 'left' ? 'left-4 min-[1224px]:left-[35px]' : 'right-4 min-[1224px]:right-[35px]' }} bottom-4 z-[60] grid size-14 place-items-center rounded-full bg-[#19bd4b] shadow-[0_12px_35px_rgba(25,189,75,.35)] transition duration-200 hover:shadow-[0_16px_42px_rgba(25,189,75,.45)] min-[1224px]:bottom-[35px] min-[1224px]:size-[64px]"><img src="/storage/figma/header-footer/whatsapp.svg" class="size-7 brightness-0 invert min-[1224px]:size-8" alt=""></a>
    @endif
    <dialog id="private-public-alert" aria-labelledby="private-public-alert-title" class="m-auto w-[min(390px,calc(100vw-32px))] overflow-hidden rounded-[24px] border border-white/80 bg-white p-0 text-[#132b3b] shadow-[0_28px_90px_rgba(7,19,27,.32)] backdrop:bg-[#132b3b]/45 backdrop:backdrop-blur-sm">
        <div class="p-6">
            <div class="flex items-start gap-4"><span class="grid size-12 shrink-0 place-items-center rounded-[16px] bg-[#edf3f7] text-[#1954b7]"><svg class="size-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 8v4m0 4h.01M10.29 3.86 2.82 16.33A2 2 0 0 0 4.53 19h14.94a2 2 0 0 0 1.71-2.67L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div class="min-w-0 grow"><h2 id="private-public-alert-title" class="text-[20px] font-semibold leading-6">Estás en Zona Clientes</h2><p class="mt-2 text-[14px] leading-5 text-[#65747c]">Para volver al sitio público, primero tenés que cerrar tu sesión.</p></div><button type="button" data-private-alert-close class="grid size-9 shrink-0 place-items-center rounded-full text-[22px] leading-none text-[#65747c] transition hover:bg-[#f1f4f5]" aria-label="Cerrar alerta">×</button></div>
            <div class="mt-6 flex justify-end gap-3"><button type="button" data-private-alert-close class="h-10 rounded-[10px] px-4 text-[13px] font-semibold text-[#52636b] transition hover:bg-[#f1f4f5]">Quedarme aquí</button><form method="POST" action="{{ route('client.logout') }}">@csrf<button class="h-10 rounded-[10px] bg-[#132b3b] px-4 text-[13px] font-semibold text-white transition hover:bg-[#1954b7]">Cerrar sesión</button></form></div>
        </div>
    </dialog>
    <script>
        document.querySelectorAll('[data-private-public-link]').forEach(function (trigger) {
            trigger.addEventListener('click', function () { document.getElementById('private-public-alert')?.showModal(); });
        });
        document.querySelectorAll('[data-private-alert-close]').forEach(function (trigger) {
            trigger.addEventListener('click', function () { document.getElementById('private-public-alert')?.close(); });
        });
        const accountMenu = document.getElementById('client-account-menu');
        document.querySelectorAll('[data-client-menu-trigger]').forEach(function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.stopPropagation();
                const isOpen = !accountMenu?.classList.contains('hidden');
                accountMenu?.classList.toggle('hidden', isOpen);
                document.querySelectorAll('[data-client-menu-trigger]').forEach(function (button) { button.setAttribute('aria-expanded', String(!isOpen)); });
            });
        });
        document.addEventListener('click', function (event) {
            if (!accountMenu?.contains(event.target) && !(event.target instanceof Element && event.target.closest('[data-client-menu-trigger]'))) {
                accountMenu?.classList.add('hidden');
                document.querySelectorAll('[data-client-menu-trigger]').forEach(function (button) { button.setAttribute('aria-expanded', 'false'); });
            }
        });
    </script>
</body>
</html>

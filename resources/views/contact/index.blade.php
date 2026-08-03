@extends('layouts.public')
@section('title', $page->seo_title ?: 'Contacto | Fitinox')
@section('meta_description', $page->seo_description ?: 'Contactate con el equipo de Fitinox.')
@section('content')
<section data-public-hero class="relative h-[240px] overflow-hidden min-[1224px]:h-[301px]">
    <img src="{{ Storage::url($page->hero_image ?: 'figma/contact/hero.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center opacity-80">
    <div class="absolute inset-0 bg-gradient-to-l from-[#1954b7] to-[#132b3b] mix-blend-multiply"></div>
    <div data-public-hero-content class="fitinox-content-1224 relative h-full text-white">
        <p class="pt-[24px] text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; Contacto</p>
        <h1 class="absolute bottom-10 left-0 text-[32px] leading-[1.2] font-semibold sm:text-[36px] min-[1224px]:top-[202px] min-[1224px]:bottom-auto min-[1224px]:w-[529px] min-[1224px]:text-[40px]">{{ $page->hero_title ?: 'Contacto' }}</h1>
    </div>
</section>

@if(session('success'))
<div id="contact-success" role="status" class="fixed top-[120px] right-6 z-[70] w-[min(390px,calc(100vw-48px))] overflow-hidden rounded-[20px] border border-white/70 bg-white/95 shadow-[0_24px_70px_rgba(7,19,27,.22)] backdrop-blur-xl">
    <div class="flex items-start gap-3 p-4"><span class="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#e9f8f0] text-[#17845c]"><svg class="size-6" viewBox="0 0 24 24" fill="none"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span><div class="grow pt-0.5"><p class="text-[13px] font-semibold text-[#132b3b]">Mensaje enviado</p><p class="mt-1 text-[11px] leading-[17px] text-[#65747c]">{{ session('success') }}</p></div><button type="button" onclick="this.closest('#contact-success').remove()" class="grid size-8 place-items-center rounded-full text-[#8b979d]" aria-label="Cerrar">×</button></div>
</div>
@endif

@php
    $address = $settings['contact_address'] ?? $settings['address'] ?? 'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina';
    $salesEmail = $settings['contact_email'] ?? $settings['email_sales'] ?? 'ventas@fittinox.com.ar';
    $toContactList = function ($serialized, $fallback) {
        $decoded = is_string($serialized) ? json_decode($serialized, true) : null;
        $values = is_array($decoded) ? $decoded : preg_split('/\R/u', (string) ($serialized ?: $fallback));
        return collect($values)->flatMap(function ($value) {
            $numbers = [];
            foreach (preg_split('/\s+\/\s+/u', trim((string) $value)) ?: [] as $part) {
                $part = trim($part);
                $digits = preg_replace('/\D/', '', $part);
                if (strlen($digits) >= 8 || empty($numbers)) {
                    $numbers[] = $part;
                } else {
                    $numbers[array_key_last($numbers)] .= ' / '.$part;
                }
            }
            return $numbers;
        })->filter()->unique()->values();
    };
    $phones = $toContactList($settings['contact_phone_numbers'] ?? null, $settings['phone'] ?? "+54 (011) 4738-5562 /\n+54 (011) 4764-2389 / 6647/8");
    $whatsapps = $toContactList($settings['contact_whatsapp_numbers'] ?? null, $settings['whatsapp'] ?? "54 9 11 4073-2016\n11 2237-2899\n11 5145-7853");
    $contactLatitude = (float) ($settings['contact_latitude'] ?? '-34.5728');
    $contactLongitude = (float) ($settings['contact_longitude'] ?? '-58.5416');
    $mapAddress = $address;
    if (app()->getLocale() === 'en') {
        $mapAddress = app(\App\Services\DeepLTranslationService::class)->translateMany([$address])[$address] ?? $address;
    }
    $inputClass = 'mt-[5px] h-[45px] w-full rounded-[4px] border border-[#dedfe0] bg-white px-[12px] text-[16px] text-[#35363a] outline-none transition focus:border-[#1954b7] focus:ring-2 focus:ring-[#1954b7]/10';
@endphp
<section class="relative mx-auto w-[calc(100%-40px)] max-w-[1224px] py-[80px] pb-[67px] xl:min-h-[1158px] xl:w-full xl:py-0">
    <div class="text-[16px] leading-[24px] text-[#35363a] xl:absolute xl:top-[80px] xl:left-[8px] xl:w-[319px]">
        <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($address) }}" target="_blank" rel="noreferrer" class="flex min-h-[69px] gap-[11px]"><img src="/storage/figma/header-footer/map-pin.svg" class="mt-[2px] size-5 shrink-0 brightness-0" alt=""><span class="w-[288px]">{{ $address }}</span></a>
        <a href="mailto:{{ $salesEmail }}" class="mt-[9px] flex items-center gap-[11px]"><img src="/storage/figma/header-footer/mail.svg" class="size-5 shrink-0 brightness-0" alt=""><span>{{ $salesEmail }}</span></a>
        <div class="mt-[18px] flex items-start gap-[11px]"><img src="/storage/figma/header-footer/phone.svg" class="mt-[2px] size-5 shrink-0 brightness-0" alt="">@if($phones->count() === 1)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phones->first()) }}" class="block">{{ $phones->first() }}</a>@else<button type="button" onclick="document.getElementById('phone-picker').showModal()" class="relative block max-w-[288px] cursor-pointer text-left text-[16px] leading-[24px] text-[#35363a] transition-colors duration-200 hover:text-[#1954b7] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] focus-visible:ring-offset-2" aria-haspopup="dialog" aria-label="Elegir teléfono de contacto">{{ $phones->implode(' / ') }}</button>@endif</div>
        <div class="mt-[18px] flex items-start gap-[11px]"><img src="/storage/figma/header-footer/whatsapp.svg" class="mt-[2px] size-5 shrink-0 brightness-0" alt="">@if($whatsapps->count() === 1)<a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapps->first()) }}" target="_blank" rel="noreferrer" class="block">{{ $whatsapps->first() }}</a>@else<button type="button" onclick="document.getElementById('whatsapp-picker').showModal()" class="relative block max-w-[288px] cursor-pointer text-left text-[16px] leading-[24px] text-[#35363a] transition-colors duration-200 hover:text-[#1954b7] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] focus-visible:ring-offset-2" aria-haspopup="dialog" aria-label="Elegir WhatsApp de contacto">{{ $whatsapps->implode(' / ') }}</button>@endif</div>
    </div>

    <form method="POST" action="{{ route('contact.store') }}" class="mt-12 grid gap-x-[25px] gap-y-[24px] xl:absolute xl:top-[80px] xl:left-[416px] xl:mt-0 xl:w-[807px] xl:grid-cols-[391px_391px]">
        @csrf
        <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true"><input type="hidden" name="form_started_at" value="{{ now()->timestamp }}">
        <label class="text-[16px] leading-[24px] text-[#35363a]">Nombre y Apellido*<input name="name" value="{{ old('name') }}" required class="{{ $inputClass }}">@error('name')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-[16px] leading-[24px] text-[#35363a]">E-mail*<input type="email" name="email" value="{{ old('email') }}" required class="{{ $inputClass }}">@error('email')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-[16px] leading-[24px] text-[#35363a]">Teléfono*<input name="phone" value="{{ old('phone') }}" required class="{{ $inputClass }}">@error('phone')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
        <label class="text-[16px] leading-[24px] text-[#35363a]">Empresa<input name="company" value="{{ old('company') }}" class="{{ $inputClass }}"></label>
        <label class="text-[16px] leading-[24px] text-[#35363a]">Mensaje*<textarea name="message" required class="mt-[5px] h-[144px] w-full resize-none rounded-[4px] border border-[#dedfe0] p-[12px] text-[16px] leading-[24px] outline-none transition focus:border-[#1954b7] focus:ring-2 focus:ring-[#1954b7]/10">{{ old('message') }}</textarea>@error('message')<span class="mt-1 block text-[11px] text-red-600">{{ $message }}</span>@enderror</label>
        <div class="relative h-[173px]">@if(config('services.turnstile.site_key'))<div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-action="contact"></div>@endif<p class="absolute top-[147px] left-0 text-[16px] leading-[24px] text-[#35363a]">*Datos obligatorios</p><button type="submit" class="absolute top-[137px] right-0 inline-flex h-[36px] w-[163px] items-center justify-center whitespace-nowrap rounded-[7px] bg-[#132b3b] px-5 text-[13px] font-medium tracking-[.39px] text-white">ENVIAR MENSAJE</button></div>
    </form>

    <div data-reveal class="relative mt-[64px] h-[500px] overflow-hidden rounded-[14px] border border-[#dedfe0] bg-[#e7e7e7] shadow-[0_18px_45px_rgba(19,43,59,.12)] sm:h-[544px] xl:absolute xl:top-[547px] xl:left-0 xl:mt-0 xl:w-[1224px] xl:rounded-none xl:shadow-none" aria-label="Mapa interactivo de ubicación de Fitinox">
        <div id="contact-map" class="h-full w-full grayscale" aria-label="Mapa de ubicación de Fitinox"></div>
    </div>
</section>
@if($phones->count() > 1)
<dialog id="phone-picker" aria-labelledby="phone-picker-title" class="m-auto w-[min(420px,calc(100vw-32px))] overflow-hidden rounded-[24px] border border-white/70 bg-white p-0 text-[#132b3b] shadow-[0_28px_90px_rgba(7,19,27,.32)] backdrop:bg-[#132b3b]/45 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-6"><div class="flex items-start gap-4"><span class="grid size-12 shrink-0 place-items-center rounded-[16px] bg-[#eaf2fb] text-[#1954b7]"><img src="/storage/figma/header-footer/phone.svg" class="size-6" alt=""></span><div class="min-w-0 grow"><h2 id="phone-picker-title" class="text-[20px] font-semibold leading-6">Elegí un teléfono</h2><p class="mt-1 text-[13px] leading-5 text-[#65747c]">Seleccioná el número con el que querés comunicarte.</p></div><button type="button" onclick="this.closest('dialog').close()" class="grid size-11 shrink-0 place-items-center rounded-full text-[22px] leading-none text-[#65747c] transition hover:bg-[#f1f4f5] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20" aria-label="Cerrar selector">×</button></div><div class="mt-5 space-y-2">@foreach($phones as $phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="flex min-h-12 items-center justify-between gap-3 rounded-[14px] border border-[#dfe5e8] bg-white px-4 text-[15px] font-medium transition duration-200 hover:border-[#1954b7] hover:bg-[#edf3f7] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20"><span>{{ $phone }}</span><svg class="size-5 shrink-0 text-[#1954b7]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>@endforeach</div></div>
</dialog>
@endif
@if($whatsapps->count() > 1)
<dialog id="whatsapp-picker" aria-labelledby="whatsapp-picker-title" class="m-auto w-[min(420px,calc(100vw-32px))] overflow-hidden rounded-[24px] border border-white/70 bg-white p-0 text-[#132b3b] shadow-[0_28px_90px_rgba(7,19,27,.32)] backdrop:bg-[#132b3b]/45 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-6"><div class="flex items-start gap-4"><span class="grid size-12 shrink-0 place-items-center rounded-[16px] bg-[#eaf2fb] text-[#1954b7]"><img src="/storage/figma/header-footer/whatsapp.svg" class="size-6" alt=""></span><div class="min-w-0 grow"><h2 id="whatsapp-picker-title" class="text-[20px] font-semibold leading-6">Elegí un WhatsApp</h2><p class="mt-1 text-[13px] leading-5 text-[#65747c]">Seleccioná el número al que querés escribir.</p></div><button type="button" onclick="this.closest('dialog').close()" class="grid size-11 shrink-0 place-items-center rounded-full text-[22px] leading-none text-[#65747c] transition hover:bg-[#f1f4f5] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20" aria-label="Cerrar selector">×</button></div><div class="mt-5 space-y-2">@foreach($whatsapps as $whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noreferrer" class="flex min-h-12 items-center justify-between gap-3 rounded-[14px] border border-[#dfe5e8] bg-white px-4 text-[15px] font-medium transition duration-200 hover:border-[#1954b7] hover:bg-[#edf3f7] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20"><span>{{ $whatsapp }}</span><svg class="size-5 shrink-0 text-[#1954b7]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>@endforeach</div></div>
</dialog>
@endif
<script>window.FitinoxContactLocation = {{ Illuminate\Support\Js::from(['name' => 'Fitinox S.R.L.', 'address' => $mapAddress, 'latitude' => $contactLatitude, 'longitude' => $contactLongitude]) }};</script>
<script>window.setTimeout(() => document.getElementById('contact-success')?.remove(), 5200);</script>
@endsection

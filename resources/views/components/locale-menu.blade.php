@php($currentLocale = app()->getLocale())
<div data-no-translate data-locale-menu class="relative {{ $class ?? '' }}">
    <button type="button" data-locale-menu-trigger aria-label="Seleccionar idioma" aria-haspopup="menu" aria-expanded="false" class="inline-flex min-h-[24px] items-center gap-[3px] whitespace-nowrap text-inherit transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1954b7] focus-visible:ring-offset-2">
        <span>{{ strtoupper($currentLocale) }}</span>
        <img src="/storage/figma/header-footer/chevron-down.svg" alt="" class="size-[14px] shrink-0">
    </button>
    <div data-locale-menu-panel role="menu" aria-label="Elegir idioma" class="absolute top-[calc(100%+10px)] right-0 z-[80] w-[148px] overflow-hidden rounded-[12px] border border-[#dfe5e8] bg-white p-1.5 text-left text-[13px] text-[#132b3b] shadow-[0_16px_40px_rgba(19,43,59,.16)]">
        <a data-locale-choice lang="es" hreflang="es" role="menuitem" href="{{ route('locale.switch', 'es') }}" class="flex min-h-10 items-center rounded-[8px] px-3 transition {{ $currentLocale === 'es' ? 'bg-[#edf3f7] font-semibold text-[#1954b7]' : 'hover:bg-[#f4f7f8]' }}" @if($currentLocale === 'es') aria-current="true" @endif>Español</a>
        <a data-locale-choice lang="en" hreflang="en" role="menuitem" href="{{ route('locale.switch', 'en') }}" class="flex min-h-10 items-center rounded-[8px] px-3 transition {{ $currentLocale === 'en' ? 'bg-[#edf3f7] font-semibold text-[#1954b7]' : 'hover:bg-[#f4f7f8]' }}" @if($currentLocale === 'en') aria-current="true" @endif>English</a>
    </div>
</div>

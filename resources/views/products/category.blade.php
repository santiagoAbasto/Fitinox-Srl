@extends('layouts.public')
@section('title', $category->name.' | Fitinox')
@section('content')
<div class="mx-auto w-[calc(100%-40px)] max-w-[1224px] pt-[24px] pb-[97px] xl:w-full">
    <p class="text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a> &gt; <a href="/productos" class="font-bold">Productos</a> &gt; {{ $category->name }}</p>
    <div class="mt-[76px] grid gap-[24px] lg:grid-cols-[288px_1fr]">
        <aside class="w-full max-w-[288px]" aria-label="Categorías de productos">
            <h2 class="text-[20px] leading-[30px] font-medium text-[#132b3b]">PRODUCTOS</h2>
            <div class="mt-[10px] h-px w-full bg-[#dedfe0]" aria-hidden="true"></div>

            <div class="mt-[4px]" data-product-accordion>
                @foreach($categories as $item)
                    @php
                        $isCurrent = $item->id === $category->id;
                        $fallbackChildren = match ($item->slug) {
                            'accesorios-para-tubos' => [
                                'Conexiones Aja-Lock',
                                'Tubos sin costura ASTM A 213/269',
                                'Soportes para tubos',
                                'Conexiones SIMPLE-LOCK',
                            ],
                            default => [],
                        };
                        $childLabels = $item->children->pluck('name')->all();
                        if (count($childLabels) === 0) {
                            $childLabels = $fallbackChildren;
                        }
                    @endphp

                    <section class="border-b border-[#dedfe0]" data-accordion-item>
                        <div class="flex min-h-[32px] items-center justify-between gap-3">
                            <a
                                href="{{ route('products.category', $item) }}"
                                class="min-w-0 flex-1 text-[16px] leading-[32px] text-[#132b3b] {{ $isCurrent ? 'font-semibold' : 'font-normal' }}"
                            >
                                {{ $item->name }}
                            </a>
                            <button
                                type="button"
                                class="flex size-[32px] shrink-0 items-center justify-center text-[#132b3b]"
                                aria-expanded="{{ $isCurrent ? 'true' : 'false' }}"
                                aria-controls="category-panel-{{ $item->id }}"
                                data-accordion-trigger
                            >
                                <span class="sr-only">Mostrar opciones de {{ $item->name }}</span>
                                <svg class="size-[18px] transition-transform duration-200 {{ $isCurrent ? 'rotate-180' : '' }}" viewBox="0 0 18 18" fill="none" aria-hidden="true">
                                    <path d="M4.5 6.75L9 11.25L13.5 6.75" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>

                        <div
                            id="category-panel-{{ $item->id }}"
                            class="{{ $isCurrent && count($childLabels) ? 'block' : 'hidden' }} mt-[2px] pb-[10px]"
                            data-accordion-panel
                        >
                            @foreach($childLabels as $childIndex => $childLabel)
                                <a
                                    href="{{ route('products.category', $item) }}#{{ Str::slug($childLabel) }}"
                                    class="block pl-[10px] text-[14px] leading-[28px] text-[#35363a] transition-colors hover:text-[#1954b7] {{ $isCurrent && $childIndex === 0 ? 'font-bold' : 'font-normal' }}"
                                >
                                    {{ $childLabel }}
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </aside>

        <div class="grid content-start gap-[24px] sm:grid-cols-2 xl:grid-cols-3">
            @foreach($products as $product)
            <a data-reveal-item href="{{ route('products.show',$product) }}" class="h-[350px] overflow-hidden rounded-[6px] border border-[#dedfe0] bg-white">
                <div class="flex h-[250px] items-center justify-center px-[24px] pt-[20px] pb-[10px]"><img src="{{ Storage::url($product->main_image ?: 'products/product-placeholder.svg') }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain"></div>
                <div class="px-[24px] pt-[3px]"><p class="text-[12px] leading-[18px] font-bold text-[#1954b7]">{{ mb_strtoupper($category->name) }}</p><h3 class="mt-[5px] text-[18px] leading-[27px] font-medium text-black">{{ $product->name }}</h3></div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-product-accordion]').forEach((accordion) => {
        accordion.querySelectorAll('[data-accordion-trigger]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const item = trigger.closest('[data-accordion-item]');
                const panel = item.querySelector('[data-accordion-panel]');
                const icon = trigger.querySelector('svg');
                const willOpen = trigger.getAttribute('aria-expanded') !== 'true';

                accordion.querySelectorAll('[data-accordion-trigger]').forEach((otherTrigger) => {
                    const otherItem = otherTrigger.closest('[data-accordion-item]');
                    otherTrigger.setAttribute('aria-expanded', 'false');
                    otherTrigger.querySelector('svg')?.classList.remove('rotate-180');
                    otherItem.querySelector('[data-accordion-panel]')?.classList.add('hidden');
                });

                if (willOpen) {
                    trigger.setAttribute('aria-expanded', 'true');
                    icon?.classList.add('rotate-180');
                    panel?.classList.remove('hidden');
                }
            });
        });
    });
</script>
@endsection

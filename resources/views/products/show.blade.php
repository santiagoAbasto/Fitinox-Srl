@extends('layouts.public')
@section('title', $product->name.' | Fitinox')
@section('content')
<div class="fitinox-content-1224 py-5 sm:py-6">
    <p class="flex flex-wrap gap-x-1 text-[12px] leading-[18px]"><a href="/" class="font-bold">Inicio</a><span>&gt;</span><a href="/productos" class="font-bold">Productos</a><span>&gt;</span><a href="{{ route('products.category',$product->category) }}" class="font-bold">{{ $product->category->name }}</a><span>&gt;</span><span>{{ $product->name }}</span></p>
    <section data-reveal class="mt-10 grid gap-10 min-[1224px]:mt-[76px] min-[1224px]:grid-cols-[600px_1fr] min-[1224px]:gap-[26px]">
        @php($productImages = array_slice(array_values(array_unique(array_filter(array_merge([$product->main_image], $product->gallery ?? [])))), 0, 3))
        @php($hasProductVideo = ($product->video_type === 'youtube' && $product->youtube_url) || ($product->video_type === 'upload' && $product->video_path))
        <div class="grid gap-3 min-[1224px]:grid-cols-[80px_1fr] min-[1224px]:gap-[24px]" data-product-gallery>
            <div class="order-2 flex gap-3 overflow-x-auto pb-1 min-[1224px]:order-1 min-[1224px]:block min-[1224px]:space-y-[10px] min-[1224px]:overflow-visible min-[1224px]:pb-0">
                @foreach($productImages as $image)
                    <button type="button" data-gallery-image="{{ Storage::url($image) }}" aria-label="Ver imagen {{ $loop->iteration }} de {{ $product->name }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="relative size-16 shrink-0 overflow-hidden rounded-[4px] border bg-white transition-colors sm:size-[72px] min-[1224px]:size-[80px] {{ $loop->first ? 'border-2 border-fitinox' : 'border-[#dedfe0]' }}"><img src="{{ Storage::url($image) }}" class="h-full w-full object-contain" alt=""></button>
                @endforeach
                @if($hasProductVideo)
                    <button type="button" data-gallery-video aria-label="Ver video de {{ $product->name }}" aria-pressed="false" class="relative size-16 shrink-0 overflow-hidden rounded-[4px] border border-[#dedfe0] bg-white transition-colors sm:size-[72px] min-[1224px]:size-[80px]">
                        <img src="{{ Storage::url($product->main_image ?: 'products/product-placeholder.svg') }}" class="h-full w-full object-contain opacity-70" alt="">
                        <span class="absolute inset-0 grid place-items-center bg-[#132b3b]/20"><span class="grid size-8 place-items-center rounded-full bg-[#132b3b]/85 text-white"><svg class="ml-0.5 size-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 5.75V18.25L18 12L8 5.75Z" fill="currentColor"/></svg></span></span>
                    </button>
                @endif
            </div>
            <div class="group relative order-1 h-[320px] overflow-hidden rounded-[4px] border border-[#dedfe0] bg-white sm:h-[440px] min-[1224px]:order-2 min-[1224px]:!h-[496px]" data-product-media>
                <div
                    class="absolute inset-0 bg-contain bg-center bg-no-repeat"
                    style="background-image: url('{{ Storage::url($product->main_image ?: 'products/product-placeholder.svg') }}');"
                    data-gallery-image-stage
                    role="img"
                    aria-label="{{ $product->name }}"
                >
                </div>
                @if($hasProductVideo)
                    <div class="absolute inset-0 hidden bg-black" data-gallery-video-stage>
                        @if($product->video_type === 'youtube')
                            <iframe class="h-full w-full" data-video-src="{{ $product->youtube_url }}{{ str_contains($product->youtube_url, '?') ? '&' : '?' }}autoplay=1&mute=1&controls=1&modestbranding=1&playsinline=1" title="Video de {{ $product->name }}" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                        @else
                            <video class="h-full w-full object-contain" muted playsinline controls preload="metadata" data-product-video><source src="{{ Storage::url($product->video_path) }}"></video>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="relative min-w-0 min-[1224px]:h-[496px]"><p class="text-[12px] font-bold text-[#1954b7]">{{ mb_strtoupper($product->category->name) }}</p><h1 class="mt-4 border-b border-[#dedfe0] pb-4 text-[30px] leading-[1.2] font-medium text-fitinox sm:text-[32px] min-[1224px]:mt-[25px] min-[1224px]:leading-[38px]">{{ $product->name }}</h1>@if($product->description)<div class="mt-5 max-h-none overflow-visible pr-0 text-[16px] leading-[1.6] min-[1224px]:mt-6 min-[1224px]:max-h-[300px] min-[1224px]:overflow-y-auto min-[1224px]:pr-2">{!! $product->description !!}</div>@endif<a href="{{ route('products.datasheet', $product) }}" target="_blank" class="mt-8 inline-flex min-h-12 w-full items-center justify-center rounded-[7px] border border-fitinox bg-white px-5 text-[13px] font-medium tracking-[.03em] text-fitinox transition hover:bg-[#edf3f7] min-[1224px]:absolute min-[1224px]:bottom-0 min-[1224px]:left-0 min-[1224px]:mt-0 min-[1224px]:h-[36px] min-[1224px]:min-h-0 min-[1224px]:w-[280px]">FICHA TÉCNICA</a></div>
    </section>
    <section data-reveal class="mt-14 sm:mt-16 min-[1224px]:mt-[80px]"><h2 class="border-b border-[#dedfe0] pb-4 text-[28px] leading-[1.2] font-medium text-fitinox sm:text-[32px] sm:leading-[38px]">Especificaciones técnicas</h2>
        <div @class(['mt-6 grid gap-6 min-[1224px]:mt-[20px] min-[1224px]:gap-x-[24px]', 'min-[1224px]:grid-cols-2' => $splitVariantTables])>
            @foreach($variantGroups as $chunk)
            @php($requiresHorizontalScroll = !$splitVariantTables && $characteristicColumns->count() > 5)
            <div @class(['max-w-full', 'overflow-x-auto' => $requiresHorizontalScroll])>
                <table class="product-variants-table w-full text-[16px] leading-[24px] text-[#35363a] min-[768px]:table-auto" style="--variant-columns: 2; min-width: {{ $requiresHorizontalScroll ? max(980, 330 + ($characteristicColumns->count() * 150)) : 0 }}px">
                    <thead><tr class="h-[50px] border-b border-[#dedfe0] text-left text-[16px] font-semibold text-fitinox">
                        @foreach($characteristicColumns as $column)<th class="px-[12px]">{{ $column['label'] }}</th>@endforeach
                        <th class="px-[12px]">Código</th><th class="w-[132px]"></th>
                    </tr></thead>
                    <tbody>@foreach($chunk as $variant)<tr class="border border-[#dedfe0] bg-white min-[768px]:h-[50px] min-[768px]:border-x-0 min-[768px]:border-t-0 min-[768px]:odd:bg-[#f5f5f5]">
                        @foreach($characteristicColumns as $column)<td data-label="{{ $column['label'] }}" class="variant-cell min-[768px]:px-[12px]">{{ $variant->characteristicValue($column['key']) ?: '—' }}</td>@endforeach
                        <td data-label="Código" class="variant-cell min-[768px]:whitespace-nowrap min-[768px]:px-[12px]">{{ $variant->code }}</td><td class="variant-action min-[768px]:pr-[12px] min-[768px]:text-right"><a href="{{ route('quote.index', ['variant' => $variant->id, 'quantity' => 1]) }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-[7px] border border-fitinox px-5 text-[13px] font-medium tracking-[.03em] text-fitinox transition hover:bg-[#edf3f7] min-[768px]:h-[36px] min-[768px]:min-h-0 min-[768px]:w-[108px]">CONSULTAR</a></td>
                    </tr>@endforeach</tbody>
                </table>
            </div>
            @endforeach
        </div>
    </section>
    @if($related->isNotEmpty())<section data-reveal class="py-[80px]"><h2 class="text-[32px] font-medium text-fitinox">Productos relacionados</h2><div class="mt-[32px] grid gap-[24px] sm:grid-cols-2 lg:grid-cols-4">@foreach($related as $item)<x-product-card :product="$item" />@endforeach</div></section>@endif
</div>

<script>
    document.querySelectorAll('[data-product-gallery]').forEach((gallery) => {
        const imageStage = gallery.querySelector('[data-gallery-image-stage]');
        const videoStage = gallery.querySelector('[data-gallery-video-stage]');
        const controls = gallery.querySelectorAll('[data-gallery-image], [data-gallery-video]');

        const select = (control) => {
            controls.forEach((item) => {
                const active = item === control;
                item.setAttribute('aria-pressed', String(active));
                item.classList.toggle('border-2', active);
                item.classList.toggle('border-fitinox', active);
                item.classList.toggle('border-[#dedfe0]', !active);
            });

            const videoSelected = control.hasAttribute('data-gallery-video');
            imageStage?.classList.toggle('hidden', videoSelected);
            videoStage?.classList.toggle('hidden', !videoSelected);

            const video = videoStage?.querySelector('video');
            const iframe = videoStage?.querySelector('iframe');
            if (videoSelected) {
                if (iframe && !iframe.getAttribute('src')) iframe.src = iframe.dataset.videoSrc || '';
                video?.play().catch(() => {});
            } else {
                video?.pause();
                if (iframe?.getAttribute('src')) iframe.removeAttribute('src');
                if (imageStage && control.dataset.galleryImage) {
                    imageStage.style.backgroundImage = `url(${JSON.stringify(control.dataset.galleryImage)})`;
                }
            }
        };

        controls.forEach((control) => control.addEventListener('click', () => select(control)));
    });
</script>
@endsection

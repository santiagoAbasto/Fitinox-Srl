@props(['product'])

@php
    $imagePath = $product->main_image ?: 'products/product-placeholder.svg';
    $imageUrl = Storage::url($imagePath);
    if ($product->main_image && $product->updated_at) {
        $imageUrl .= '?v='.$product->updated_at->timestamp;
    }
@endphp

<a
    href="{{ route('products.show', $product) }}"
    {{ $attributes->class('group block min-h-[380px] overflow-hidden rounded-[6px] border border-[#dedfe0] bg-white transition-colors hover:border-[#b9c6cc] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1954b7]') }}
>
    <div class="aspect-square w-full overflow-hidden bg-white">
        <img
            src="{{ $imageUrl }}"
            alt="{{ $product->name }}"
            class="h-full w-full object-contain"
            loading="lazy"
        >
    </div>
    <div class="px-[24px] pt-[12px] pb-[20px]">
        <p class="text-[12px] leading-[18px] font-bold text-[#1954b7]">{{ mb_strtoupper($product->category->name) }}</p>
        <h3 class="mt-[5px] text-[18px] leading-[27px] font-medium tracking-[-.01em] text-black">{{ $product->name }}</h3>
    </div>
</a>

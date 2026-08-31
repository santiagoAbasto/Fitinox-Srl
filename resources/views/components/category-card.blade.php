@props(['category', 'image' => null])

@php
    $resolvedImage = $image ?: $category->resolvedImage();
    $imageUrl = $resolvedImage
        ? Storage::url($resolvedImage)
        : asset('storage/products/product-placeholder.svg');
@endphp

<a
    href="{{ route('products.category', $category) }}"
    {{ $attributes->merge(['class' => 'group relative h-[300px] overflow-hidden rounded-[6px] bg-[#f5f5f5] sm:h-[350px] min-[1224px]:!h-[392px]']) }}
>
    <img src="{{ $imageUrl }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
    <div class="absolute inset-0 bg-gradient-to-b from-white/0 to-black/75"></div>
    <div class="absolute right-[18px] bottom-[29px] left-[18px] flex items-end justify-between text-white">
        <strong class="max-w-[220px] text-[20px] leading-[30px]">{{ $category->name }}</strong>
        <span class="text-[30px] font-light" aria-hidden="true">+</span>
    </div>
</a>

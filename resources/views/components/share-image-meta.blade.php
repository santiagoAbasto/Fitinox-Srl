@props(['settings', 'fallbackImage' => null, 'pageImage' => null])

@php
    $headerImagePath = $fallbackImage ?: 'figma/header-footer/header-logo.png';
    $fallbackShareImage = Storage::disk('public')->exists($headerImagePath)
        ? $headerImagePath
        : 'figma/header-footer/footer-logo-mask.png';
    $shareImagePath = !empty($pageImage)
        ? $pageImage
        : (!empty($settings['seo_og_image'] ?? null) ? $settings['seo_og_image'] : $fallbackShareImage);
    $shareImageUrl = url(Storage::url($shareImagePath));
@endphp

<meta property="og:image" content="{{ $shareImageUrl }}">
<meta property="og:image:secure_url" content="{{ $shareImageUrl }}">
<meta property="og:image:alt" content="Fitinox">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $shareImageUrl }}">

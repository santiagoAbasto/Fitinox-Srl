<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 30px 38px 64px; }
    * { box-sizing: border-box; }
    body { margin: 0; color: #35363a; font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 1.45; }
    .header { position: relative; width: 100%; height: 76px; border-bottom: 2px solid #132b3b; }
    .logo { position: absolute; top: 5px; left: 0; width: 186px; }
    .document-cell { position: absolute; top: 3px; left: 437px; width: 280px; }
    .document-title { position: absolute; top: 0; left: 174px; width: 106px; color: #132b3b; font-size: 10px; font-weight: bold; letter-spacing: .75px; text-transform: uppercase; }
    .document-reference { position: absolute; top: 22px; left: 92px; width: 188px; color: #66747b; font-size: 7.5px; line-height: 1.65; }
    .document-reference strong { color: #35363a; }
    .eyebrow { margin-top: 24px; color: #1954b7; font-size: 9px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
    h1 { margin: 7px 0 0; color: #132b3b; font-size: 26px; line-height: 1.15; }
    .title-rule { width: 42px; height: 3px; margin-top: 12px; background: #1954b7; }
    .intro { margin-top: 20px; width: 100%; border-collapse: separate; border-spacing: 0; }
    .image-cell { width: 43%; padding-right: 24px; vertical-align: top; }
    .image-frame { height: 224px; padding: 14px; background: #fafbfb; border: 1px solid #dedfe0; border-radius: 6px; }
    .product-image { width: 100%; height: 194px; object-fit: contain; }
    .summary-cell { vertical-align: top; }
    .summary-box { height: 224px; padding: 0; overflow: hidden; background: white; border: 1px solid #dedfe0; border-radius: 6px; }
    .summary-heading { padding: 12px 17px; color: white; background: #132b3b; font-size: 14px; font-weight: bold; }
    .summary-content { padding: 13px 17px 11px; }
    .facts { width: 100%; margin-bottom: 11px; border-collapse: collapse; }
    .facts td { padding: 5px 0; border-bottom: 1px solid #eceff0; font-size: 8.5px; }
    .facts tr:last-child td { border-bottom: 0; }
    .fact-label { width: 38%; color: #66747b; font-weight: bold; }
    .fact-value { color: #132b3b; text-align: right; }
    .product-copy { margin: 0; color: #53636b; font-size: 8.5px; line-height: 1.55; }
    .tag { display: inline-block; margin-top: 10px; padding: 5px 9px; color: #1954b7; background: #f3f7ff; border-radius: 10px; font-size: 7px; font-weight: bold; letter-spacing: .25px; }
    .section-title { margin: 28px 0 10px; padding-bottom: 8px; color: #132b3b; border-bottom: 1px solid #dedfe0; font-size: 17px; }
    table.specs { width: 100%; border-collapse: collapse; }
    .specs thead { display: table-header-group; }
    .specs tr { page-break-inside: avoid; }
    .specs th { padding: 10px 9px; color: white; background: #132b3b; text-align: left; font-size: 8px; letter-spacing: .3px; text-transform: uppercase; }
    .specs td { padding: 9px; border-bottom: 1px solid #dedfe0; font-size: 9px; }
    .specs tbody tr:nth-child(odd) td { background: #f5f5f5; }
    .code { color: #132b3b; font-weight: bold; }
    .notes { margin-top: 20px; padding: 14px 16px; color: #53636b; background: #f5f7f8; border-left: 3px solid #1954b7; font-size: 8px; }
    .footer { position: fixed; right: 0; bottom: -43px; left: 0; height: 28px; padding-top: 8px; color: #6c787e; border-top: 1px solid #dedfe0; font-size: 7px; line-height: 1.25; }
    .footer-brand { position: absolute; top: 8px; left: 0; width: 48%; white-space: nowrap; }
    .footer-contact { position: absolute; top: 8px; right: 82px; width: 230px; text-align: right; white-space: nowrap; }
</style>
</head>
<body>
@php
    $en = $locale === 'en';
    $t = static fn (?string $value) => $translations[trim((string) $value)] ?? (string) $value;
    $copy = $en ? [
        'sheet' => 'Technical data sheet', 'product' => 'Product', 'revision' => 'Revision', 'updated' => 'Updated',
        'information' => 'Product information', 'category' => 'Category', 'sku' => 'General SKU', 'variants' => 'Variants',
        'configurations' => 'configurations', 'quality' => 'Quality', 'consult' => 'Contact us',
        'description' => 'Fitinox industrial component designed for fluid control and conveyance applications.',
        'available' => '360° VIEW AVAILABLE', 'media' => 'MULTIMEDIA INFORMATION ON REQUEST',
        'specifications' => 'Technical specifications', 'code' => 'Code', 'detail' => 'Detail', 'tube' => 'Tube', 'thread' => 'Thread',
        'noVariants' => 'Please contact the sales team to confirm available specifications.',
        'notes' => 'Dimensions and specifications may be updated without prior notice. For selection, compatibility, availability or certifications, please contact us quoting the exact product code.',
        'footer' => 'Components for control systems and fluid conditions',
    ] : [
        'sheet' => 'Ficha técnica', 'product' => 'Producto', 'revision' => 'Revisión', 'updated' => 'Actualizado',
        'information' => 'Información del producto', 'category' => 'Categoría', 'sku' => 'SKU general', 'variants' => 'Variantes',
        'configurations' => 'configuraciones', 'quality' => 'Calidad', 'consult' => 'Consultar',
        'description' => 'Componente industrial Fitinox diseñado para aplicaciones de conducción y control de fluidos.',
        'available' => 'VISTA 360° DISPONIBLE', 'media' => 'INFORMACIÓN MULTIMEDIA A CONSULTAR',
        'specifications' => 'Especificaciones técnicas', 'code' => 'Código', 'detail' => 'Detalle', 'tube' => 'Tubo', 'thread' => 'Rosca',
        'noVariants' => 'Consulte especificaciones disponibles con el equipo comercial.',
        'notes' => 'Las dimensiones y especificaciones pueden actualizarse sin previo aviso. Para selección, compatibilidad, disponibilidad o certificaciones, consulte indicando el código exacto del producto.',
        'footer' => 'Componentes para sistemas de control y condiciones de fluidos',
    ];
@endphp
    <div class="header">
        @if($logo)<img src="{{ $logo }}" class="logo" alt="Fitinox">@endif
        <div class="document-cell" align="right">
            <div class="document-title" align="right">{{ $copy['sheet'] }}</div>
            <div class="document-reference" align="right">
                {{ $copy['product'] }} &nbsp;·&nbsp; <strong>FT-{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}</strong> &nbsp;·&nbsp; {{ $copy['revision'] }} 01<br>
                {{ $copy['updated'] }}: {{ $generatedAt->format('d/m/Y') }}
            </div>
        </div>
    </div>
    <div class="eyebrow">{{ $t($product->category->name) }}</div>
    <h1>{{ $t($product->name) }}</h1>
    <div class="title-rule"></div>
    <table class="intro"><tr>
        <td class="image-cell"><div class="image-frame">@if($image)<img src="{{ $image }}" class="product-image" alt="{{ $product->name }}">@endif</div></td>
        <td class="summary-cell"><div class="summary-box">
            <div class="summary-heading">{{ $copy['information'] }}</div>
            <div class="summary-content">
                <table class="facts">
                    <tr><td class="fact-label">{{ $copy['category'] }}</td><td class="fact-value">{{ $t($product->category->name) }}</td></tr>
                    @if($product->sku)<tr><td class="fact-label">{{ $copy['sku'] }}</td><td class="fact-value">{{ $product->sku }}</td></tr>@endif
                    <tr><td class="fact-label">{{ $copy['variants'] }}</td><td class="fact-value">{{ $product->variants->count() }} {{ $copy['configurations'] }}</td></tr>
                    <tr><td class="fact-label">{{ $copy['quality'] }}</td><td class="fact-value">{{ $product->variants->pluck('quality')->filter()->unique()->map($t)->join(' / ') ?: $copy['consult'] }}</td></tr>
                </table>
                <p class="product-copy">{{ $t(strip_tags($product->summary ?: $product->description ?: $copy['description'])) }}</p>
                <span class="tag">{{ $product->video_type ? $copy['available'] : $copy['media'] }}</span>
            </div>
        </div></td>
    </tr></table>
    <h2 class="section-title">{{ $copy['specifications'] }}</h2>
    <table class="specs">
        <thead><tr><th style="width:28%">{{ $copy['code'] }}</th><th style="width:24%">{{ $copy['detail'] }}</th><th style="width:16%">{{ $copy['tube'] }}</th><th style="width:16%">{{ $copy['thread'] }}</th><th style="width:16%">{{ $copy['quality'] }}</th></tr></thead>
        <tbody>@forelse($product->variants as $variant)<tr><td class="code">{{ $variant->code }}</td><td>{{ $t($variant->detail ?: $product->name) }}</td><td>{{ $variant->tube ?: '—' }}</td><td>{{ $variant->thread ?: '—' }}</td><td>{{ $variant->quality ? $t($variant->quality) : '—' }}</td></tr>@empty<tr><td colspan="5">{{ $copy['noVariants'] }}</td></tr>@endforelse</tbody>
    </table>
    <div class="notes">{{ $copy['notes'] }}</div>
    <div class="footer">
        <div class="footer-brand"><strong>FITINOX S.R.L.</strong> · {{ $copy['footer'] }}</div>
        <div class="footer-contact">ventas@fittinox.com.ar · www.fittinox.com.ar</div>
    </div>
</body>
</html>

<!doctype html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 34px 38px 64px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #35363a; font-size: 10px; line-height: 1.45; }
        .document { width: 100%; }
        .header { border-bottom: 2px solid #132b3b; padding: 10px 0 18px; }
        .brand { width: 39%; vertical-align: middle; }
        .brand img { width: 198px; height: auto; }
        .document-meta { width: 61%; vertical-align: middle; }
        .header-details { width: 100%; border-collapse: collapse; }
        .header-details td { vertical-align: middle; white-space: nowrap; }
        .header-kind { color: #1954b7; font-size: 8px; font-weight: bold; letter-spacing: 1px; }
        .header-code { color: #132b3b; font-size: 11px; font-weight: bold; letter-spacing: .25px; text-align: center; }
        .header-date { color: #71808a; font-size: 8px; text-align: right; }
        .muted { color: #71808a; }
        .summary { margin-top: 25px; border: 1px solid #dedfe0; border-left: 4px solid #1954b7; }
        .summary td { padding: 11px 14px; vertical-align: top; }
        .summary .label { color: #71808a; font-size: 8px; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
        .summary .value { margin-top: 3px; color: #132b3b; font-size: 10px; font-weight: bold; }
        .status { display: inline-block; padding: 4px 8px; border: 1px solid #b9d6f6; background: #edf5ff; color: #1954b7; font-size: 8px; font-weight: bold; }
        .section-title { margin: 27px 0 10px; color: #132b3b; font-size: 14px; font-weight: bold; }
        .items { width: 100%; border-collapse: collapse; }
        .items th { padding: 10px 11px; background: #132b3b; color: #fff; font-size: 9px; text-align: left; }
        .items th:first-child { border-radius: 4px 0 0 0; }
        .items th:last-child { border-radius: 0 4px 0 0; }
        .items td { padding: 10px 11px; border-bottom: 1px solid #dedfe0; vertical-align: middle; }
        .items tbody tr:nth-child(even) td { background: #f7f9fa; }
        .right { text-align: right; }
        .delivery { margin-top: 24px; }
        .delivery td { width: 50%; padding: 0 5px 0 0; vertical-align: top; }
        .delivery td + td { padding: 0 0 0 10px; }
        .delivery-card { min-height: 73px; border: 1px solid #dedfe0; padding: 11px 13px; }
        .card-label { color: #71808a; font-size: 8px; font-weight: bold; letter-spacing: .7px; text-transform: uppercase; }
        .card-value { margin-top: 4px; color: #35363a; }
        .totals { width: 245px; margin: 26px 0 0 auto; border-collapse: collapse; }
        .totals td { padding: 5px 0; }
        .totals .amount { color: #132b3b; font-weight: bold; text-align: right; }
        .totals .total-row td { padding-top: 10px; border-top: 1px solid #132b3b; color: #132b3b; font-size: 12px; font-weight: bold; }
        .footer { position: fixed; right: 0; bottom: -36px; left: 0; border-top: 1px solid #dedfe0; padding-top: 9px; color: #71808a; font-size: 8px; }
        .footer-right { float: right; text-align: right; }
    </style>
</head>
<body>
@php
    $en = $locale === 'en';
    $t = static fn (?string $value) => $translations[trim((string) $value)] ?? (string) $value;
    $copy = $en ? [
        'kind' => 'CLIENT AREA · COMMERCIAL RECEIPT', 'title' => 'Order receipt', 'issued' => 'Issued on', 'client' => 'Client', 'deliveryDate' => 'Delivery date',
        'status' => 'Status', 'orderTotal' => 'Order total', 'details' => 'Product details', 'product' => 'Product', 'code' => 'Code', 'quantity' => 'Quantity',
        'unitPrice' => 'Unit price', 'amount' => 'Amount', 'delivery' => 'Delivery method', 'pickup' => 'Factory pickup', 'transport' => 'Carrier delivery',
        'notes' => 'Customer notes', 'noNotes' => 'No additional notes.', 'subtotal' => 'Subtotal', 'discount' => 'Discount', 'total' => 'Total',
        'footer' => 'Components for control systems and fluid conditions',
        'received' => 'Received', 'processing' => 'Processing', 'quoted' => 'Quoted', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ] : [
        'kind' => 'ZONA CLIENTES · COMPROBANTE COMERCIAL', 'title' => 'Comprobante de pedido', 'issued' => 'Emitido el', 'client' => 'Cliente', 'deliveryDate' => 'Fecha de entrega',
        'status' => 'Estado', 'orderTotal' => 'Total del pedido', 'details' => 'Detalle de productos', 'product' => 'Producto', 'code' => 'Código', 'quantity' => 'Cantidad',
        'unitPrice' => 'Precio unitario', 'amount' => 'Importe', 'delivery' => 'Modalidad de entrega', 'pickup' => 'Retiro en fábrica', 'transport' => 'Entrega a transporte',
        'notes' => 'Observaciones del cliente', 'noNotes' => 'Sin observaciones adicionales.', 'subtotal' => 'Subtotal', 'discount' => 'Descuento', 'total' => 'Total',
        'footer' => 'Componentes para sistemas de control y condiciones de fluidos',
        'received' => 'Recibido', 'processing' => 'En proceso', 'quoted' => 'Cotizado', 'completed' => 'Completado', 'cancelled' => 'Cancelado',
    ];
    $deliveryDate = $order->delivery_date ?? $order->created_at;
@endphp
<div class="document">
    <table class="header" width="100%" cellspacing="0" cellpadding="0"><tr><td class="brand">@if($logo)<img src="{{ $logo }}" alt="Fitinox">@else<strong style="font-size:24px;letter-spacing:-1px">FITINOX <span style="font-size:10px">S.R.L.</span></strong>@endif</td><td class="document-meta"><table class="header-details" cellspacing="0" cellpadding="0"><tr><td class="header-kind" width="39%">{{ $copy['kind'] }}</td><td class="header-code" width="34%">{{ $copy['title'] }} · {{ $order->number }}</td><td class="header-date" width="27%">{{ $copy['issued'] }} {{ $order->created_at->format('d/m/Y') }}</td></tr></table></td></tr></table>

    <table class="summary" width="100%" cellspacing="0" cellpadding="0"><tr><td width="34%"><div class="label">{{ $copy['client'] }}</div><div class="value">{{ $client->name }}</div><div class="muted">{{ $client->company ?: $client->email }}</div></td><td width="25%"><div class="label">{{ $copy['deliveryDate'] }}</div><div class="value">{{ $deliveryDate->format('d/m/Y') }}</div></td><td width="21%"><div class="label">{{ $copy['status'] }}</div><div class="value"><span class="status">{{ $copy[$order->status] ?? ucfirst($order->status) }}</span></div></td><td width="20%"><div class="label">{{ $copy['orderTotal'] }}</div><div class="value">USD {{ number_format((float) $order->total, 2, ',', '.') }}</div></td></tr></table>

    <div class="section-title">{{ $copy['details'] }}</div>
    <table class="items" cellspacing="0" cellpadding="0"><thead><tr><th>{{ $copy['product'] }}</th><th>{{ $copy['code'] }}</th><th class="right">{{ $copy['quantity'] }}</th><th class="right">{{ $copy['unitPrice'] }}</th><th class="right">{{ $copy['amount'] }}</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{ $t($item->product_name) }}</strong></td><td>{{ $item->code ?: '—' }}</td><td class="right">{{ $item->quantity }}</td><td class="right">USD {{ number_format((float) $item->unit_price, 2, ',', '.') }}</td><td class="right"><strong>USD {{ number_format((float) $item->line_total, 2, ',', '.') }}</strong></td></tr>@endforeach</tbody></table>

    <table class="delivery" width="100%" cellspacing="0" cellpadding="0"><tr><td><div class="delivery-card"><div class="card-label">{{ $copy['delivery'] }}</div><div class="card-value">{{ $order->delivery_method === 'pickup' ? $copy['pickup'] : $copy['transport'] }}</div>@if($order->delivery_method === 'transport' && $order->transport_name)<div class="muted">{{ $order->transport_name }}@if($order->delivery_destination) · {{ $order->delivery_destination }}@endif</div>@endif</div></td><td><div class="delivery-card"><div class="card-label">{{ $copy['notes'] }}</div><div class="card-value">{{ $order->notes ? $t($order->notes) : $copy['noNotes'] }}</div></div></td></tr></table>

    <table class="totals" cellspacing="0" cellpadding="0"><tr><td>{{ $copy['subtotal'] }}</td><td class="amount">USD {{ number_format((float) $order->subtotal, 2, ',', '.') }}</td></tr><tr><td>{{ $copy['discount'] }}</td><td class="amount">USD {{ number_format((float) $order->discount_total, 2, ',', '.') }}</td></tr><tr class="total-row"><td>{{ $copy['total'] }}</td><td class="right">USD {{ number_format((float) $order->total, 2, ',', '.') }}</td></tr></table>
</div>
<div class="footer"><span><strong>FITINOX S.R.L.</strong> · {{ $copy['footer'] }}</span><span class="footer-right">{{ $client->email }} · www.fittinox.com.ar</span></div>
</body>
</html>

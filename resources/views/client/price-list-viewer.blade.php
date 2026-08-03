<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $priceList->name }} | Lista de precios Fitinox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-screen overflow-hidden bg-[#eef2f4] font-[Montserrat] text-[#132b3b]">
    <header class="flex h-[72px] items-center justify-between gap-5 border-b border-[#d8dcdd] bg-white px-5 md:px-8">
        <div class="min-w-0"><p class="text-[10px] font-semibold uppercase tracking-[.16em] text-[#1954b7]">{{ strtoupper($extension) }} · LISTA DE PRECIOS</p><h1 class="mt-1 truncate text-[15px] font-semibold">{{ $priceList->name }}</h1></div>
        <div class="flex shrink-0 items-center gap-2"><button type="button" onclick="window.close()" class="hidden h-10 items-center rounded-[7px] border border-[#d8dcdd] px-4 text-[11px] font-semibold sm:inline-flex">CERRAR</button><a href="{{ $downloadUrl }}" class="inline-flex h-10 items-center rounded-[7px] bg-[#132b3b] px-4 text-[11px] font-semibold text-white transition hover:bg-[#1954b7]">DESCARGAR</a></div>
    </header>
    <main class="h-[calc(100vh-72px)]">
        @if($office && $isLocal)
            <div class="grid h-full place-items-center p-6"><section class="max-w-[620px] rounded-2xl border border-[#d8dcdd] bg-white p-8 text-center shadow-[0_18px_60px_rgba(19,43,59,.08)]"><div class="mx-auto grid size-14 place-items-center rounded-2xl bg-[#edf3f7] text-[18px] font-bold text-[#1954b7]">{{ strtoupper($extension) }}</div><h2 class="mt-5 text-[20px] font-semibold">El documento está listo para verse online</h2><p class="mt-3 text-[13px] leading-6 text-[#66767e]">Word, Excel y PowerPoint se visualizan con Microsoft Office Online. En desarrollo local Microsoft no puede acceder al archivo; en un dominio público se abrirá automáticamente.</p><a href="{{ $downloadUrl }}" class="mt-6 inline-flex h-11 items-center rounded-[7px] bg-[#132b3b] px-6 text-[11px] font-semibold text-white">DESCARGAR PARA REVISAR</a></section></div>
        @elseif($office)
            <iframe title="{{ $priceList->name }}" src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($rawUrl) }}" class="h-full w-full border-0" allowfullscreen></iframe>
        @elseif(in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true))
            <div class="flex h-full items-center justify-center overflow-auto p-6"><img src="{{ $rawUrl }}" alt="{{ $priceList->name }}" class="max-h-full max-w-full rounded-lg bg-white object-contain shadow-xl"></div>
        @else
            <iframe title="{{ $priceList->name }}" src="{{ $rawUrl }}" class="h-full w-full border-0 bg-white"></iframe>
        @endif
    </main>
</body>
</html>

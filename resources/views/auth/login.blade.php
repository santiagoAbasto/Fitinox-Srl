<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso administrativo | Fitinox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen bg-white">
    <main class="grid min-h-screen lg:grid-cols-[46%_54%]">
        <section class="relative hidden overflow-hidden bg-fitinox lg:block">
            <img src="/storage/figma/about.png" alt="Producción Fitinox" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-fitinox/80 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-fitinox via-transparent to-fitinox/30"></div>
            <div class="relative flex h-full flex-col justify-between p-14 xl:p-20">
                <a href="/" class="inline-block w-fit"><img src="/storage/figma/header-footer/footer-logo-mask.png" alt="Fitinox" class="h-auto w-[225px] brightness-0 invert"></a>
                <div class="max-w-[520px] border-l-2 border-white pl-7 text-white">
                    <h2 class="text-[38px] leading-[1.15] font-semibold">Gestión de contenidos Fitinox</h2>
                    <p class="mt-5 max-w-[440px] text-[15px] leading-7 text-white/75">Administrá la información institucional, el catálogo de productos, documentos y consultas desde un único lugar.</p>
                </div>
                <p class="text-xs text-white/45">Fitinox S.R.L.</p>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-[430px]">
                <a href="/" class="mb-14 block w-fit lg:hidden"><img src="/storage/figma/header-footer/header-logo.png" alt="Fitinox" class="h-auto w-[220px]"></a>
                <p class="text-[13px] font-semibold tracking-[.14em] text-fitinox-accent">ADMINISTRACIÓN</p>
                <h1 class="mt-3 text-[34px] leading-tight font-semibold text-fitinox">Iniciar sesión</h1>
                <p class="mt-3 text-sm leading-6 text-gray-500">Ingresá con tu cuenta para administrar el sitio.</p>

                <form method="post" action="{{ route('login.store') }}" class="mt-10">
                    @csrf
                    <label for="email" class="block text-sm font-medium text-[#35363a]">Email</label>
                    <input id="email" name="email" value="{{ old('email') }}" type="email" autocomplete="email" required autofocus
                        class="mt-2 h-12 w-full rounded-[6px] border border-[#cfd3d6] bg-white px-4 text-[15px] text-[#35363a] outline-none transition placeholder:text-gray-400 focus:border-fitinox focus:ring-2 focus:ring-fitinox/10"
                        placeholder="nombre@empresa.com">

                    <div class="mt-6 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-[#35363a]">Contraseña</label>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="mt-2 h-12 w-full rounded-[6px] border border-[#cfd3d6] bg-white px-4 text-[15px] text-[#35363a] outline-none transition focus:border-fitinox focus:ring-2 focus:ring-fitinox/10">

                    @error('email')
                        <p class="mt-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</p>
                    @enderror

                    <label class="mt-5 flex w-fit cursor-pointer items-center gap-3 text-sm text-gray-600">
                        <input name="remember" type="checkbox" value="1" class="size-4 rounded border-gray-300 accent-[#132b3b]">
                        Recordarme
                    </label>

                    <button class="mt-8 flex h-12 w-full items-center justify-center rounded-[7px] bg-fitinox px-5 text-sm font-semibold tracking-[.04em] text-white transition hover:bg-[#1b3b50] focus:outline-none focus:ring-2 focus:ring-fitinox focus:ring-offset-2">
                        INGRESAR
                    </button>
                </form>

                <div class="mt-10 border-t border-gray-200 pt-6">
                    <a href="/" class="text-sm font-medium text-fitinox hover:underline">Volver al sitio público</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

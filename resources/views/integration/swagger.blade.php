<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Fitinox · Documentación de integración</title>
    <link rel="stylesheet" href="{{ asset('vendor/swagger-ui/swagger-ui.css') }}">
    <style>
        body { margin: 0; background: #f6f8fa; }
        .swagger-ui .topbar { background-color: #102e3f; }
        .swagger-ui .topbar-wrapper::before { content: 'FITINOX · INTEGRACIÓN'; color: #fff; font: 700 14px Arial, sans-serif; letter-spacing: .06em; }
        .swagger-ui .topbar-wrapper img, .swagger-ui .topbar .download-url-wrapper { display: none; }
        .swagger-ui .info .title { color: #102e3f; }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="{{ asset('vendor/swagger-ui/swagger-ui-bundle.js') }}"></script>
    <script src="{{ asset('vendor/swagger-ui/swagger-ui-standalone-preset.js') }}"></script>
    <script>
        window.ui = SwaggerUIBundle({
            url: @json(url('/api/openapi.json')),
            dom_id: '#swagger-ui',
            deepLinking: true,
            persistAuthorization: false,
            displayRequestDuration: true,
            filter: true,
            // El token se ingresa manualmente en Authorize y no queda persistido.
            tryItOutEnabled: true,
            validatorUrl: null,
            presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
            layout: 'StandaloneLayout',
        });
    </script>
</body>
</html>

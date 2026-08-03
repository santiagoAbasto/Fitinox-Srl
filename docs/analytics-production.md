# Puesta en producción de Analítica Web

## Variables

```dotenv
ANALYTICS_ENABLED=true
ANALYTICS_RETENTION_DAYS=365
ANALYTICS_SESSION_MINUTES=30
ANALYTICS_HASH_KEY=base64:una-clave-independiente-y-larga
ANALYTICS_EXCLUDE_PATHS=/admin,/clientes
VITE_POSTHOG_KEY=
VITE_POSTHOG_HOST=https://us.i.posthog.com
VITE_POSTHOG_RECORDINGS=false
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=
TURNSTILE_HOSTNAME=www.fitinox.com.ar
MAXMIND_DATABASE_PATH=/var/lib/GeoIP/GeoLite2-City.mmdb
CLOUDFLARE_API_TOKEN=
CLOUDFLARE_ZONE_ID=
```

## Instalación

```bash
npm install
php artisan migrate --force
npm run build
php artisan optimize
```

El scheduler debe ejecutar `php artisan schedule:run` cada minuto. Si `QUEUE_CONNECTION` no es `sync`, mantener un worker supervisado con `php artisan queue:work --tries=3`.

## Cloudflare

Crear reglas para desafiar tráfico anómalo en `/admin/login`, `/contacto`, `/presupuesto` y `/analytics/events`; aplicar rate limiting por IP; conservar `CF-Connecting-IP`; bloquear agentes automatizados maliciosos y permitir bots verificados. Turnstile debe usar widgets separados con actions `login`, `contact` y `quote`, hostname exacto y secretos sólo en Laravel.

## PostHog

Crear un proyecto por ambiente, excluir `/admin/*` y `/clientes/*`, desactivar grabaciones por defecto y habilitarlas sólo tras revisar consentimiento y retención. Enmascarar inputs y texto sensible; no capturar contraseñas, tokens, datos bancarios ni cabeceras. Las claves públicas usan prefijo `VITE_`; nunca exponer una clave personal de API.

## MaxMind

Descargar GeoLite2 City con una cuenta autorizada, actualizar el `.mmdb` periódicamente y montar la ruta en `MAXMIND_DATABASE_PATH`. La interfaz debe describir ciudad y coordenadas como aproximadas. La instalación actual aprovecha `CF-IPCountry`; la resolución City requiere instalar el lector oficial de MaxMind antes de activar ese archivo.

## Operación

- Ejecutar `php artisan analytics:summarize --date=AAAA-MM-DD` para reconstruir un día.
- Ejecutar `php artisan analytics:prune` para aplicar retención manualmente.
- Revisar tamaño e índices al superar diez millones de eventos y particionar por fecha en MySQL si fuese necesario.
- Respaldar agregados y política de privacidad antes de reducir retención.

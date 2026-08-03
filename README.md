# FITINOX

Sitio web corporativo, catálogo comercial y plataforma de administración de FITINOX S.R.L. El proyecto reúne el portal público, un CMS interno, analítica web propia y una zona privada para clientes.

## Funcionalidades

- Sitio institucional bilingüe (español e inglés).
- Home administrable con sliders de imagen, video o YouTube.
- Catálogo de categorías, productos, variantes y fichas técnicas en PDF.
- Buscador público y relaciones entre productos.
- Secciones administrables de empresa, calidad, descargas, videos y distribuidores.
- Solicitudes de presupuesto y mensajes de contacto.
- Registro, aprobación y gestión de clientes.
- Zona privada con productos, listas de precios, carrito y pedidos.
- Newsletter y administración de suscriptores.
- Analítica propia con sesiones, conversiones, dispositivos y geolocalización aproximada.
- Integraciones opcionales con PostHog, Cloudflare, Turnstile, DeepL y MaxMind GeoLite2.
- Encabezados de seguridad, CSP configurable, rate limiting y niveles de acceso administrativo.

## Stack

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8.3+, Laravel 13 |
| Base de datos | MySQL / MariaDB |
| Frontend administrativo | React 19, Inertia 3, TypeScript |
| Sitio público | Blade, Tailwind CSS 4, JavaScript |
| Build | Vite 8 |
| Mapas y gráficos | MapLibre GL, Leaflet, Recharts |
| Movimiento | Motion |
| Documentos | Dompdf |

## Requisitos

- PHP 8.3 o superior con las extensiones habituales de Laravel.
- Composer 2.
- Node.js compatible con Vite 8 y npm.
- MySQL o MariaDB.
- Acceso de escritura a `storage` y `bootstrap/cache`.

## Instalación local

```bash
git clone https://github.com/santiagoAbasto/Fitinox-Srl.git
cd Fitinox-Srl
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configurar las credenciales de base de datos en `.env` y ejecutar:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Para trabajar con Vite en modo desarrollo:

```bash
npm run dev
```

Nunca se debe versionar el archivo `.env`, credenciales, claves privadas ni tokens de servicios externos.

## Variables de entorno relevantes

Partir siempre de `.env.example`. Además de la configuración estándar de Laravel, el proyecto utiliza:

| Variable | Propósito |
| --- | --- |
| `APP_URL` | URL canónica de la aplicación. |
| `APP_TIMEZONE` | Zona horaria de Laravel. |
| `DEEPL_API_KEY` | Traducción automática opcional. |
| `ANALYTICS_ENABLED` | Activa la analítica propia. |
| `ANALYTICS_HASH_KEY` | Secreto para anonimizar identificadores analíticos. |
| `VITE_POSTHOG_KEY` | Proyecto público opcional de PostHog. |
| `MAXMIND_DATABASE_PATH` | Ruta privada a `GeoLite2-City.mmdb`. |
| `CLOUDFLARE_API_TOKEN` | Token opcional y de solo lectura. |
| `CLOUDFLARE_ZONE_ID` | Zona consultada por la integración Cloudflare. |
| `TURNSTILE_SITE_KEY` | Clave pública de Cloudflare Turnstile. |
| `TURNSTILE_SECRET_KEY` | Clave privada de Turnstile. |
| `SECURITY_CSP_ENFORCE` | Cambia CSP de report-only a obligatoria. |

### GeoLite2

La base de MaxMind debe permanecer fuera del directorio público. La ubicación recomendada es:

```text
storage/app/geoip/GeoLite2-City.mmdb
```

Ejemplo local:

```env
MAXMIND_DATABASE_PATH=/ruta/absoluta/al/proyecto/storage/app/geoip/GeoLite2-City.mmdb
```

## Comandos habituales

```bash
# Ejecutar pruebas
php artisan test --compact

# Compilar frontend para producción
npm run build

# Limpiar y reconstruir cachés de Laravel
php artisan optimize:clear
php artisan optimize

# Aplicar migraciones en producción
php artisan migrate --force
```

## Estructura principal

```text
app/                  Backend, modelos, servicios y middleware
config/               Configuración de Laravel e integraciones
database/             Migraciones, factories y seeders
deployment/cpanel/    Archivos públicos preparados para cPanel
public/               Document root local y build de Vite
resources/js/         Aplicación administrativa React/Inertia
resources/views/      Sitio público Blade
routes/                Rutas web y comandos
storage/app/public/   Imágenes, videos y documentos públicos
tests/                 Pruebas automatizadas
```

## Despliegue en cPanel

La aplicación Laravel debe permanecer fuera del directorio público. La estructura usada en producción es:

```text
/home/fitinoxosolecom/FITINOX       Aplicación completa
/home/fitinoxosolecom/public_html   Document root del dominio
```

### Con SSH

```bash
cd /home/fitinoxosolecom/FITINOX
git pull --ff-only origin main
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan optimize:clear
php artisan migrate --force
npm ci
npm run build
rsync -a public/ /home/fitinoxosolecom/public_html/
cp deployment/cpanel/public_html/index.php /home/fitinoxosolecom/public_html/index.php
cp deployment/cpanel/public_html/.htaccess /home/fitinoxosolecom/public_html/.htaccess
sed -i 's#/aplicacion#/FITINOX#' /home/fitinoxosolecom/public_html/index.php
test -e /home/fitinoxosolecom/public_html/storage || \
  ln -s /home/fitinoxosolecom/FITINOX/storage/app/public /home/fitinoxosolecom/public_html/storage
php artisan optimize
```

En este hosting, `public_html/storage` debe apuntar a:

```text
/home/fitinoxosolecom/FITINOX/storage/app/public
```

> `php artisan storage:link` crea el enlace dentro de `FITINOX/public`. Si el document root real es `public_html`, se debe crear allí el enlace equivalente o configurar el dominio para usar `FITINOX/public` directamente.

### Sin SSH

1. Subir o actualizar la aplicación en `/home/fitinoxosolecom/FITINOX`.
2. Copiar el contenido de `FITINOX/public` a `public_html`; no copiar la carpeta `public` como subcarpeta.
3. Copiar `deployment/cpanel/public_html/.htaccess` a `public_html/.htaccess`.
4. Copiar `deployment/cpanel/public_html/index.php` a `public_html/index.php`.
5. Confirmar que `$applicationPath` en ese `index.php` apunte a `/home/fitinoxosolecom/FITINOX`.
6. Crear `public_html/storage` como enlace hacia `FITINOX/storage/app/public`.
7. Verificar permisos `0755` o `0775` en `storage`, `bootstrap/cache` y el almacenamiento público.
8. Si se cambió configuración y existe `bootstrap/cache/config.php`, eliminar ese archivo para que Laravel vuelva a leer `.env`.

El build compilado debe terminar exactamente en:

```text
public_html/build/manifest.json
public_html/build/assets/
```

No debe quedar anidado como `public_html/build/build`.

## Archivos públicos y cargas

Todos los archivos administrables se guardan mediante el disco `public` de Laravel:

```text
storage/app/public
```

La URL equivalente es:

```text
/storage/{ruta-del-archivo}
```

Si imágenes, videos o documentos responden 404 en producción, comprobar primero que `public_html/storage` sea un enlace válido y que el archivo exista en `FITINOX/storage/app/public`.

## Pruebas y control de calidad

Antes de desplegar:

```bash
php artisan test --compact
npm run build
```

También se recomienda comprobar manualmente:

- Home y navegación responsive.
- Carga y reproducción de sliders y videos.
- Catálogo, búsqueda y fichas técnicas.
- Formularios públicos y envío de correo.
- Acceso administrativo y permisos por rol.
- Registro, aprobación y portal de clientes.
- URLs bajo `/storage`.
- Consola del navegador y política CSP en report-only.

## Seguridad

- Mantener `APP_DEBUG=false` en producción.
- No colocar `.env`, GeoLite2 ni documentos privados dentro de `public_html`.
- Empezar con `SECURITY_CSP_ENFORCE=false`, revisar la consola y activar la política únicamente cuando no existan fuentes legítimas pendientes.
- Usar tokens de mínimo privilegio y rotarlos si se exponen.
- Mantener dependencias, copias de seguridad y certificados actualizados.

## Mantenimiento

El repositorio es privado y corresponde al proyecto FITINOX. Los cambios de esquema deben realizarse mediante migraciones; no se deben modificar tablas de producción manualmente salvo durante una recuperación controlada.

Antes de reemplazar `public_html/build` o archivos de storage, conservar una copia recuperable hasta verificar el sitio en producción.

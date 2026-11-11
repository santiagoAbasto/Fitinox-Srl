# FITINOX

Plataforma web corporativa y comercial para administrar contenido, catálogo industrial, clientes, pedidos e integraciones de FITINOX.

## Descripción

FITINOX centraliza en una sola aplicación la presencia web de una empresa industrial y los procesos que conectan su catálogo con clientes y responsables comerciales. El sistema reúne un sitio público bilingüe, un CMS privado, un catálogo jerárquico con variantes técnicas, formularios comerciales, newsletter, analítica propia y una zona de clientes con precios, carrito y pedidos.

El código corresponde a un proyecto cliente funcional y preparado para un despliegue tradicional con Apache/cPanel. Incluye migraciones, datos iniciales, documentación operativa y pruebas automatizadas. El repositorio no permite verificar por sí solo la disponibilidad actual del entorno productivo.

## Funcionalidades principales

- Sitio institucional en español e inglés, con traducciones persistidas y uso opcional de DeepL.
- CMS para páginas, portada, sliders, categorías, productos, descargas, documentos de calidad, videos, distribuidores, SEO y datos de contacto.
- Catálogo con categorías jerárquicas, productos relacionados, variantes, características técnicas dinámicas, precios, stock, búsqueda y fichas PDF.
- Importación y exportación de inventario mediante XLSX/CSV, además de carga masiva de imágenes y videos por claves de asociación.
- Solicitudes de presupuesto, mensajes de contacto y enlaces configurables de WhatsApp.
- Registro y aprobación de clientes, descuentos por cuenta, listas de precios privadas, carrito, pedidos, adjuntos y comprobantes PDF.
- Newsletter con suscriptores, bajas, campañas, adjuntos y registro de entregas.
- Analítica propia de sesiones, navegación, eventos, conversiones, dispositivos, Web Vitals, procedencia y geolocalización aproximada.
- Panel analítico con gráficos y mapa geográfico; integración opcional con PostHog.
- API JSON versionada para sincronizar artículos, clientes, stock, eventos comerciales y estados de pedidos con un sistema de gestión externo.
- Roles diferenciados para superadministración, administración, edición de contenido y clientes.

## Arquitectura

La solución es un monolito Laravel con organización MVC y una capa de servicios para procesos como importación, traducción, analítica, protección de formularios e integración. Las páginas públicas y la zona de clientes se renderizan en servidor con Blade. El panel administrativo usa React y TypeScript mediante Inertia, por lo que comparte rutas, sesión y validaciones con el backend sin mantener una API separada para el panel.

Eloquent gestiona la persistencia. La API REST de integración intercambia JSON, exige un token Bearer y aplica idempotencia a las escrituras. Una bandeja de salida persistente desacopla la captura de altas y cambios comerciales de su posterior consumo por el sistema externo. Los comandos programados agregan y depuran la analítica; no se encontraron jobs de aplicación ejecutados en cola y los correos se envían de forma síncrona.

```text
Visitantes ──> Blade SSR ───────────────┐
Clientes ───> Portal Blade ─────────────┤
Administración ──> React + Inertia ─────┼──> Laravel MVC + Services ──> Eloquent ──> Base de datos
Sistema de gestión <──> API JSON ───────┘              │
                                                       ├──> Storage / correo
                                                       └──> DeepL, Turnstile, PostHog y GeoLite2
```

## Stack tecnológico

### Backend

- PHP 8.3 o superior.
- Laravel 13.8, Eloquent ORM e Inertia Laravel 3.2.
- Dompdf 3.1 para documentos PDF.
- Servicios HTTP de Laravel para DeepL y Cloudflare Turnstile.

### Frontend

- React 19.2, TypeScript 7 e Inertia React 3.6 para administración.
- Blade para el sitio público y la zona privada de clientes.
- Tailwind CSS 4, Vite 7 y Motion 12.
- Recharts 3, MapLibre GL 6 y Leaflet 1.9 para gráficos y mapas.

### Base de datos

- Eloquent ORM y migraciones de Laravel.
- MySQL/MariaDB como configuración documentada de despliegue; SQLite está configurado para desarrollo y pruebas.

### Testing

- PHPUnit 12.5 con pruebas unitarias y de funcionalidad de Laravel.
- Factories, base SQLite en memoria y fakes de almacenamiento/correo donde corresponde.

### DevOps / Infraestructura

- Build reproducible con Composer, npm y Vite.
- Plantillas de `index.php` y `.htaccess` para Apache/cPanel con la aplicación fuera del directorio público.
- Scheduler de Laravel para agregación y retención de analítica.
- No se encontraron Docker, Kubernetes ni pipelines CI/CD versionados.

### Integraciones

- DeepL para traducción opcional con caché local y fallback al texto original.
- Cloudflare Turnstile para formularios públicos y acceso.
- PostHog opcional para eventos y grabaciones configurables.
- MaxMind GeoLite2 y cabeceras de Cloudflare para geolocalización aproximada.
- SMTP mediante Laravel Mail.
- API privada para un sistema de gestión externo.

## Modelo de datos

El modelo usa Eloquent. `Category` admite jerarquías autorreferenciadas y agrupa `Product`; cada producto tiene variantes con código, precios, stock y características técnicas en JSON. Los productos pueden relacionarse entre sí mediante una tabla pivote ordenada.

`User` representa cuentas administrativas y clientes. Los pedidos conservan una cabecera y líneas con una copia de nombres, códigos y valores comerciales para mantener el histórico. Las listas de precios y adjuntos de pedidos se sirven mediante rutas autenticadas. Newsletter, consultas, presupuestos, contenidos y traducciones tienen entidades propias.

La analítica normaliza sesiones, vistas, secciones, eventos, conversiones y resúmenes diarios. La integración externa conserva artículos y clientes recibidos, hashes de origen, fechas de actualización, solicitudes idempotentes y eventos de salida. Las migraciones definen claves foráneas, índices, identificadores únicos y reglas de borrado. El checkout, la ingesta analítica y las escrituras idempotentes usan transacciones.

## Seguridad

- Autenticación por sesión de Laravel, contraseñas con cast `hashed`, regeneración de sesión al ingresar e invalidación al salir.
- Middleware para cuentas activas y roles `super_admin`, `admin`, `editor` y `client`; las operaciones comerciales y de usuarios elevan el nivel requerido.
- Policies que comprueban acceso a pedidos y listas de precios; los archivos privados se entregan tras autorización con `no-store` y `nosniff`.
- Validación de requests, límites de tamaño y listas MIME. `SecureUpload` rechaza extensiones ejecutables o dobles sospechosas y valida dimensiones de imágenes.
- Rate limiting específico para login, registro, formularios, búsqueda, analítica, uploads, checkout y API de integración.
- Turnstile valida token, acción y hostname; en producción puede fallar cerrado si falta configuración. Los formularios también usan honeypot y control de tiempo.
- El HTML enriquecido pasa por un sanitizador de allowlist. Las consultas usan Eloquent o bindings; las pruebas incluyen payloads XSS y SQL injection.
- CSRF permanece activo en las rutas web. El middleware añade CSP configurable, restricciones de framing y tipos MIME, políticas de origen/referrer/permisos y HSTS opcional.
- La analítica filtra propiedades permitidas y almacena hashes de IP en los eventos normales en lugar de la IP completa.
- Secretos y archivos de entorno están excluidos mediante `.gitignore`; `.env.example` contiene solo nombres y valores de referencia.

La CSP está en modo Report-Only por defecto y todavía admite `unsafe-inline`. Antes de un despliegue público también debe revisarse el seeder: contiene datos operativos y una credencial inicial que no debe reutilizarse sin reemplazo.

## APIs e integraciones

La aplicación expone una API REST versionada y una especificación OpenAPI 3.1 generada desde el backend. El contrato permite consultar y actualizar lotes de artículos y clientes, consumir y confirmar eventos de salida, y actualizar estados y referencias de pedidos.

Todas las operaciones de integración requieren un Bearer token configurado fuera del código y tienen rate limiting. Los upserts exigen una clave de idempotencia, comparan hashes del payload, rechazan la reutilización de la misma clave con otro contenido y evitan sobrescribir registros con datos de origen más antiguos. El stock solo se vincula cuando el código externo coincide con una variante existente; importar un cliente externo no crea automáticamente una cuenta de acceso.

Las integraciones con DeepL, Turnstile, PostHog, MaxMind y correo se activan por configuración. La aplicación mantiene fallback funcional cuando la traducción o la base geográfica no están disponibles.

## Testing y calidad

La suite combina pruebas unitarias y feature tests. Cubre sanitización HTML, headers, roles, búsquedas con payloads maliciosos, API e idempotencia, flujo de outbox, analítica, navegación responsive, jerarquías del catálogo, suscripciones e importación/exportación de inventario y medios.

Verificación local realizada el 8 de septiembre de 2026:

```text
48 tests passed, 277 assertions
```

Comando utilizado, sin cachear resultados:

```bash
./vendor/bin/phpunit --do-not-cache-result
```

No se encontraron pruebas E2E de navegador, pruebas de carga, configuración de análisis estático ni una pipeline CI versionada.

## DevOps y despliegue

El repositorio documenta un despliegue en Apache/cPanel: código y datos privados quedan fuera del document root, mientras `public/`, el build de Vite y el enlace de storage público se exponen desde el directorio web. Las plantillas de Apache bloquean dotfiles, manifiestos, archivos de entorno, backups, scripts y ejecutables dentro de uploads.

La operación prevista usa `composer install --no-dev`, `npm ci`, `npm run build`, migraciones, cachés de Laravel y scheduler. Existen comandos diarios para resumir métricas y aplicar retención. La configuración incluye una cola basada en base de datos y el script de desarrollo inicia un listener, aunque el código actual no define jobs encolados. Los procedimientos ampliados de seguridad, backup, rollback e incidentes están en [docs/SECURITY_PRODUCTION.md](docs/SECURITY_PRODUCTION.md).

## Instalación local

Requisitos: PHP 8.3+, Composer, Node.js/npm compatible con Vite 7 y una base de datos soportada por Laravel. La importación XLSX requiere las extensiones PHP usadas por DOM y ZIP.

1. Clonar el repositorio y entrar en su directorio.
2. Instalar dependencias:

   ```bash
   composer install
   npm ci
   ```

3. Crear la configuración local y la clave de aplicación:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configurar en `.env` la conexión de base de datos y, solo si se usarán, correo e integraciones. No reutilizar valores de producción.
5. Preparar esquema y archivos públicos:

   ```bash
   php artisan migrate
   php artisan storage:link
   ```

6. Iniciar servidor, listener, logs y Vite con el script definido por Composer:

   ```bash
   composer run dev
   ```

Para generar el frontend de producción se usa `npm run build`. El seeder incorpora contenido comercial y acceso inicial; debe revisarse y adaptarse antes de ejecutar `php artisan migrate --seed` fuera de un entorno controlado.

## Estructura del proyecto

```text
app/
├── Console/Commands/     Comandos operativos e integración
├── Http/Controllers/     Flujos públicos, privados, administrativos y API
├── Http/Middleware/      Autorización, idioma y headers de seguridad
├── Models/               Entidades Eloquent
├── Policies/             Acceso a pedidos y listas de precios
├── Rules/                Validación reforzada de uploads
└── Services/             Importación, analítica, traducción e integración
config/                   Aplicación, seguridad, analítica y servicios
database/
├── migrations/           Esquema y restricciones
└── seeders/              Contenido inicial del proyecto
deployment/cpanel/        Plantillas públicas para Apache/cPanel
docs/                     Arquitectura, seguridad, operación e integración
resources/
├── js/                   Panel React/Inertia y scripts públicos
├── views/                Blade, emails y PDFs
└── css/                  Estilos Tailwind
routes/                   Rutas web, API y comandos programados
scripts/                  Generación de plantilla de importación
tests/                    Pruebas unitarias y feature
```

## Decisiones técnicas destacables

- Combinar Blade para experiencias públicas con React/Inertia para el panel, manteniendo autenticación, rutas y validación en Laravel.
- Aislar importación, traducción, analítica y sincronización en servicios con responsabilidades concretas.
- Registrar eventos comerciales en una outbox persistente para que el sistema externo los consuma y confirme sin acoplarse al checkout.
- Aplicar idempotencia, hashes de contenido, timestamps de origen y transacciones a la sincronización por lotes.
- Separar archivos públicos de documentos de clientes y aplicar autorización al servir contenido privado.

## Desafíos técnicos resueltos

- **Problema:** sincronizar datos bidireccionales sin duplicar escrituras ni perder cambios comerciales. **Solución:** API versionada con Bearer token, claves de idempotencia, control de antigüedad y patrón outbox con confirmación.
- **Problema:** representar un catálogo industrial con jerarquías y especificaciones variables. **Solución:** categorías autorreferenciadas, variantes con características JSON e importación/exportación tabular con asociación de medios.
- **Problema:** entregar pedidos, adjuntos y listas de precios solo al cliente autorizado. **Solución:** storage privado, middleware de cliente, policies y respuestas de archivo sin caché.

## Estado del proyecto

**Proyecto cliente.** El repositorio contiene una aplicación funcional, pruebas, migraciones, documentación de producción y plantillas de despliegue específicas. La ejecución local está verificada; el estado operativo del servidor externo no forma parte de esta revisión.

## Mi contribución

Desarrollé una solución full stack sobre Laravel que conecta el sitio institucional, la gestión de contenidos y el circuito comercial. La evidencia del repositorio respalda trabajo en modelado de datos, backend MVC y servicios, interfaces Blade y React/Inertia, automatización de inventario, seguridad de acceso, analítica e integración con un sistema de gestión. No se atribuyen responsabilidades de liderazgo o gestión de equipo que el código no permite comprobar.

## Resumen para portfolio

### Portfolio Summary

Nombre:
FITINOX

Tipo:
Plataforma web corporativa y comercial para una empresa industrial

Rol:
Full Stack Developer / Software Engineer / Solution Integrator

Descripción corta:
Sistema Laravel que integra sitio público, CMS, catálogo técnico, portal de clientes, pedidos, analítica y sincronización con un sistema de gestión.

Stack principal:
PHP 8.3+, Laravel 13, Eloquent, React 19, Inertia 3, TypeScript, Blade, Tailwind CSS 4, Vite 7 y MySQL/MariaDB

Arquitectura:
Monolito MVC con renderizado Blade, panel React/Inertia, capa de servicios y API REST versionada

Seguridad:
Sesiones, roles y policies, validación, rate limiting, Turnstile, sanitización HTML, uploads restringidos, storage privado y headers configurables

Testing:
PHPUnit; 48 pruebas y 277 aserciones verificadas localmente

DevOps:
Build Composer/npm/Vite, migraciones y cachés Laravel, scheduler y plantillas Apache/cPanel; sin Docker ni CI versionados

IA:
No se encontró inteligencia artificial implementada; DeepL se usa como servicio de traducción automática

5 habilidades clave:

1. Desarrollo full stack con Laravel, React e Inertia.
2. Diseño de modelos relacionales y flujos transaccionales.
3. Integración de sistemas mediante APIs REST e idempotencia.
4. Seguridad web aplicada a roles, archivos, formularios y contenido.
5. Testing funcional y automatización de procesos de catálogo.

3 logros o aportes verificables:

- Construcción de un catálogo industrial administrable con variantes dinámicas e importación/exportación masiva.
- Implementación de un portal de clientes con descuentos, documentos privados, carrito, pedidos y PDFs.
- Integración bidireccional con un sistema de gestión mediante API autenticada, outbox e idempotencia.

Nivel de madurez:
Proyecto funcional preparado para despliegue de cliente; disponibilidad productiva no verificada desde el repositorio

Elementos que no deben publicarse:

- Tokens, claves, credenciales iniciales, archivos `.env`, rutas internas del servidor y URLs privadas.
- Datos personales o comerciales incluidos en configuración local, seeders, documentación operativa o payloads de integración.

## Evidencia técnica

| Afirmación | Evidencia |
| --- | --- |
| PHP 8.3 y Laravel 13 | `composer.json` |
| React, Inertia, TypeScript, Tailwind y Vite | `package.json`, `vite.config.js` |
| Sitio público, portal de clientes y administración | `routes/web.php`, `resources/views/`, `resources/js/Pages/` |
| Modelo relacional con Eloquent | `app/Models/`, `database/migrations/` |
| API autenticada, idempotencia y outbox | `routes/api.php`, `LegacyIntegrationController.php`, `IntegrationOutbox.php` |
| Roles, policies, rate limiting y headers | `bootstrap/app.php`, `app/Http/Middleware/`, `app/Policies/`, `AppServiceProvider.php` |
| Validación de uploads y sanitización HTML | `SecureUpload.php`, `HtmlSanitizer.php` |
| Analítica propia y tareas programadas | `AnalyticsEventController.php`, `routes/console.php`, `docs/analytics-architecture.md` |
| Pruebas automatizadas | `phpunit.xml`, `tests/` |
| Despliegue Apache/cPanel | `deployment/cpanel/`, `docs/SECURITY_PRODUCTION.md` |

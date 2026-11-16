<div align="center">
<img src="docs/readme/banner.svg" alt="FITINOX — Catálogo industrial, clientes con precios privados, pedidos e integración con gestión" width="100%">
<br/>

**Plataforma corporativa y comercial: sitio bilingüe, catálogo técnico, portal de clientes, pedidos y API de integración.**

<img alt="Proyecto cliente" src="https://img.shields.io/badge/estado-proyecto_cliente-1954b7?style=for-the-badge&labelColor=132b3b">
<img alt="Laravel 13" src="https://img.shields.io/badge/Laravel-13-ff2d20?style=flat-square&logo=laravel&logoColor=white">
<img alt="PHP 8.3+" src="https://img.shields.io/badge/PHP-8.3%2B-777bb4?style=flat-square&logo=php&logoColor=white">
<img alt="React 19" src="https://img.shields.io/badge/React-19-149eca?style=flat-square&logo=react&logoColor=white">
<img alt="Inertia.js 3" src="https://img.shields.io/badge/Inertia.js-3-9553e9?style=flat-square">
<img alt="Tailwind CSS 4" src="https://img.shields.io/badge/Tailwind_CSS-4-06b6d4?style=flat-square&logo=tailwindcss&logoColor=white">
<img alt="MySQL" src="https://img.shields.io/badge/MySQL-8-4479a1?style=flat-square&logo=mysql&logoColor=white">
</div>

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-indice.svg" width="24" height="24" alt=""> Contenido

[El problema](#-el-problema) · [La solución](#-la-solución) · [Funcionalidades](#-funcionalidades) · [Arquitectura](#-arquitectura) · [Flujos de trabajo](#-flujos-de-trabajo) · [Modelo de datos](#-modelo-de-datos) · [Criterios y decisiones](#-criterios-y-decisiones) · [Stack](#-stack) · [Estructura](#-estructura-del-repo) · [Puesta en marcha](#-puesta-en-marcha) · [Pruebas y calidad](#-pruebas-y-calidad) · [Despliegue](#-despliegue) · [Estado y pendientes](#-estado-y-pendientes) · [Licencia y autoría](#-licencia-y-autoría)

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-problema.svg" width="24" height="24" alt=""> El problema

La empresa tenía su catálogo industrial repartido entre hojas de cálculo, un sitio institucional y procesos manuales para cotizar, vender a clientes con precios distintos y llevar pedidos. Cada cambio de precio, de stock o de ficha técnica se repetía en varios lugares, y el sistema de gestión de la empresa no recibía la información comercial de forma ordenada.

## <img src="docs/readme/ic-solucion.svg" width="24" height="24" alt=""> La solución

**FITINOX** es una aplicación Laravel con cuatro zonas:

- **Sitio público** en español e inglés, renderizado con Blade: institucional, catálogo jerárquico con variantes técnicas, fichas PDF, distribuidores y presupuestos.
- **Portal de clientes**: registro con aprobación, precios y listas privadas por cuenta, carrito, pedidos y comprobantes.
- **Panel administrativo** con React e Inertia: CMS, catálogo, inventario, clientes, pedidos, newsletter y analítica.
- **API REST versionada** para que el sistema de gestión externo sincronice artículos, clientes, stock, eventos comerciales y estados de pedidos.

Roles: superadministración, administración, edición de contenido y clientes.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-funcionalidades.svg" width="24" height="24" alt=""> Funcionalidades

**Sitio público** (rol: visitante)
- Contenido institucional bilingüe con traducciones persistidas y uso opcional de DeepL.
- Catálogo con categorías jerárquicas, productos relacionados, variantes, características técnicas, búsqueda y fichas PDF.
- Solicitudes de presupuesto, mensajes de contacto y enlaces configurables de WhatsApp.
- Newsletter con suscriptores, bajas, campañas y registro de entregas.

**Portal de clientes** (rol: cliente)
- Registro y aprobación de cuentas; descuentos por cuenta y listas de precios privadas.
- Carrito, pedidos, adjuntos y comprobantes PDF.

**Panel administrativo** (roles: superadministración, administración, edición)
- CMS de páginas, portada, sliders, categorías, productos, descargas, documentos de calidad, videos, distribuidores, SEO y datos de contacto.
- Importación y exportación de inventario en XLSX y CSV; carga masiva de imágenes y videos por claves de asociación.
- Panel analítico de sesiones, navegación, eventos, conversiones, Web Vitals y procedencia, con mapa geográfico aproximado; integración opcional con PostHog.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-arquitectura.svg" width="24" height="24" alt=""> Arquitectura

Monolito Laravel con organización MVC y una capa de servicios para importación, traducción, analítica, protección de formularios e integración. El sitio y el portal de clientes se renderizan en servidor con Blade; el panel usa React y TypeScript mediante Inertia.

Eloquent gestiona la persistencia. La API de integración intercambia JSON con token Bearer e idempotencia en las escrituras. Una bandeja de salida persistente desacopla la captura de altas y cambios comerciales de su consumo por el sistema externo. Los comandos programados agregan y depuran la analítica.

<img src="docs/readme/flujo.svg" alt="Cómo viaja una petición: visitante o cliente navega y compra, Blade o Inertia responden, Laravel aplica servicios, políticas y API, MySQL guarda catálogo, pedidos y analítica" width="100%">

```mermaid
flowchart LR
    V["Visitantes"] --> B["Sitio público (Blade)"]
    C["Clientes"] --> PC["Portal de clientes (Blade)"]
    A["Administración"] --> P["Panel (React + Inertia)"]
    G["Sistema de gestión"] --> API["API REST v1 (Bearer)"]
    B --> L["Laravel MVC"]
    PC --> L
    P --> L
    API --> L
    L --> S["Services (importación, traducción, analítica)"]
    S --> EL["Eloquent"]
    EL --> DB[("MySQL")]
    L --> ST["Storage privado y público"]
    L --> EXT["DeepL, Turnstile, PostHog, GeoLite2 (opcionales)"]
    L --> ML["Correo"]
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-flujos.svg" width="24" height="24" alt=""> Flujos de trabajo

**Pedido de un cliente aprobado**

```mermaid
sequenceDiagram
    actor Cliente
    participant Portal as Portal de clientes
    participant Laravel as Laravel
    participant DB as MySQL
    participant Outbox as Bandeja de salida
    participant Gestion as Sistema de gestión
    Cliente->>Portal: Arma el carrito con precios de su cuenta
    Portal->>Laravel: Confirma el pedido
    Laravel->>DB: Guarda cabecera y líneas con copia de nombres, códigos y valores
    Laravel->>Outbox: Registra el evento de alta
    Gestion->>Outbox: Consume eventos mediante la API
    Gestion->>Laravel: Actualiza estado y referencia del pedido
    Laravel->>DB: Persiste el nuevo estado
```

**Sincronización de artículos con idempotencia**

```mermaid
sequenceDiagram
    participant Gestion as Sistema de gestión
    participant API as API REST v1
    participant Svc as Servicio de integración
    participant DB as MySQL
    Gestion->>API: Upsert por lote con clave de idempotencia
    API->>Svc: Valida Bearer, límites y payload
    Svc->>DB: Compara hash del payload y fecha de origen
    alt Misma clave, mismo contenido
        Svc-->>API: Respuesta anterior reutilizada
    else Clave reutilizada con otro contenido
        Svc-->>API: Rechazo
    else Nuevo o más reciente
        Svc->>DB: Actualiza el registro
        Svc-->>API: Resultado del lote
    end
    API-->>Gestion: Respuesta JSON
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-datos.svg" width="24" height="24" alt=""> Modelo de datos

Eloquent y migraciones con claves foráneas, índices e identificadores únicos.

```mermaid
erDiagram
    CATEGORY ||--o{ CATEGORY : subcategoria
    CATEGORY ||--o{ PRODUCT : agrupa
    PRODUCT ||--o{ PRODUCT_VARIANT : tiene
    PRODUCT }o--o{ PRODUCT : relacionado
    USER ||--o{ ORDER : realiza
    ORDER ||--o{ ORDER_LINE : contiene
    PRODUCT ||--o{ ORDER_LINE : copia
    PRODUCT {
        bigint id PK
        string slug
    }
    ORDER {
        bigint id PK
    }
    USER {
        bigint id PK
    }
```

Las líneas de pedido guardan una copia de nombres, códigos y valores comerciales para conservar el histórico. Las listas de precios y adjuntos se sirven por rutas autenticadas. La integración conserva artículos y clientes recibidos, hashes de origen, solicitudes idempotentes y eventos de salida.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-criterios.svg" width="24" height="24" alt=""> Criterios y decisiones

- **Histórico de pedidos inmutable.** Las líneas guardan copias de nombres, códigos y precios; un cambio en el catálogo no reescribe pedidos anteriores.
- **Escrituras idempotentes.** La API rechaza reutilizar una clave con otro contenido y no sobrescribe datos con información de origen más antigua.
- **Sanitización de HTML por allowlist.** El contenido enriquecido solo conserva etiquetas y atributos permitidos.
- **Analítica sin IP completa.** Los eventos guardan hashes de IP, no la dirección.
- **Fallbacks.** Traducción y base geográfica son opcionales; si no están disponibles, la aplicación sigue funcionando.

| Decisión | Por qué |
| --- | --- |
| Monolito Laravel con Blade y React/Inertia | Un solo despliegue y una sola base; el público necesita render en servidor y el panel necesita interacción. |
| Bandeja de salida para la integración | Un fallo del sistema externo no bloquea la captura de pedidos ni altas. |
| Despliegue tradicional Apache/cPanel | Así lo documenta el repositorio; el código y los datos privados quedan fuera del document root. |

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-stack.svg" width="24" height="24" alt=""> Stack

| Capa | Tecnología | Versión |
| --- | --- | --- |
| Lenguaje | PHP | ^8.3 |
| Backend | Laravel framework | ^13 (lock 13.23.0) |
| Integración panel | inertiajs/inertia-laravel | 3.2.1 |
| Frontend | React | 19.2.8 |
| Frontend | @inertiajs/react | 3.6.1 |
| Estilos | Tailwind CSS | 4.3.3 |
| Build | Vite | 7.3.6 |
| Pruebas | PHPUnit | según `composer.json` |
| Base de datos | MySQL | configurada en `.env.example` |

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-estructura.svg" width="24" height="24" alt=""> Estructura del repo

```text
app/                  controladores, servicios, políticas y modelos
routes/web.php        sitio, portal y panel
routes/api.php        API de integración
database/             migraciones, seeders y factories
resources/views/      Blade público y portal
resources/js/         panel React e Inertia (TypeScript)
deployment/cpanel/    plantillas de despliegue Apache/cPanel
docs/                 documentación de integración, seguridad y analítica
tests/                pruebas unitarias y feature
```

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-arranque.svg" width="24" height="24" alt=""> Puesta en marcha

Requisitos: PHP 8.3+, Composer, Node.js, npm y MySQL.

```bash
composer install
npm ci
cp .env.example .env              # definir base, correo y claves de servicios localmente
php artisan key:generate
php artisan migrate
php artisan storage:link
composer run dev                  # servidor, cola, logs y Vite en paralelo
```

Variables relevantes (por nombre): `DB_*`, `MAIL_*`, `QUEUE_CONNECTION`, tokens de la API de integración, Turnstile, DeepL, PostHog y GeoLite2, todos opcionales salvo los que el entorno requiera. Los valores reales no se versionan.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-pruebas.svg" width="24" height="24" alt=""> Pruebas y calidad

Pruebas unitarias y feature sobre sanitización HTML, headers, roles, búsquedas con payloads maliciosos, API e idempotencia, outbox, analítica, navegación responsive, jerarquías del catálogo, suscripciones e importación y exportación de inventario.

```bash
./vendor/bin/phpunit --do-not-cache-result
```

Registro de la versión anterior de este README: 48 pruebas y 277 aserciones aprobadas el 8 de septiembre de 2026; no se volvió a ejecutar para este cambio. No hay pruebas E2E de navegador, de carga, análisis estático ni pipeline CI versionados.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-despliegue.svg" width="24" height="24" alt=""> Despliegue

Documentado para Apache/cPanel: el código y los datos privados quedan fuera del document root, y solo `public/`, el build de Vite y el enlace de storage público se exponen. Las plantillas de Apache bloquean dotfiles, manifiestos, archivos de entorno, backups y scripts.

Secuencia prevista: `composer install --no-dev`, `npm ci`, `npm run build`, migraciones, cachés de Laravel y scheduler. Los detalles operativos están en `deployment/` y `docs/SECURITY_PRODUCTION.md`; no se reproducen aquí.

Antes de un despliegue público: revisar el seeder, que contiene datos de arranque que deben reemplazarse, y la CSP, que en el código queda en modo Report-Only por defecto.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-estado.svg" width="24" height="24" alt=""> Estado y pendientes

- **Estado:** aplicación funcional con pruebas, migraciones, documentación de producción y plantillas de despliegue. Verificación local confirmada en la versión anterior del README.
- **Último commit:** 2026-11-11, `docs: refresh project documentation and ignore deploy outputs`, en el historial local.
- **Producción:** no verificado desde el repositorio.
- **Pendientes:** reemplazar datos de arranque antes de publicar; endurecer la CSP quitando `unsafe-inline`; cola de base de datos con worker en el entorno real.

<img src="docs/readme/divider.svg" alt="" width="100%">

## <img src="docs/readme/ic-licencia.svg" width="24" height="24" alt=""> Licencia y autoría

Desarrollado por el **Ing. Santiago Abasto**.


Repositorio sin archivo de licencia. El código y el contenido son propiedad del cliente, FITINOX, salvo que se acuerde otra cosa.

# Auditoría de seguridad previa a producción

Fecha: 2026-08-03. Alcance: Laravel 13, Inertia/React/TypeScript, MySQL, Vite, Apache/PHP-FPM y despliegue cPanel. Metodología: revisión estática de rutas, controladores, modelos, vistas, frontend, configuración, migraciones, uploads y dependencias; pruebas locales no destructivas.

## Hallazgos y resolución

### Altos

- XSS almacenado en HTML enriquecido: `strip_tags` conservaba atributos activos y el contenido se imprimía sin escape. Corregido con sanitizador backend de allowlist, enlaces restringidos y pruebas con `script`, `img/onerror`, `svg/onload` y `javascript:`. Eliminados `dangerouslySetInnerHTML` innecesarios.
- Archivos privados bajo disco público: nuevos adjuntos de pedidos y listas de precios pasan al disco local; se entregan sólo mediante ruta autenticada/policy con `nosniff` y `no-store`. Se conserva fallback de lectura para archivos heredados, que deben migrarse fuera de `public/storage` durante el despliegue.
- Autorización administrativa demasiado amplia: cuenta activa obligatoria; productos/precios, clientes, newsletter y configuración requieren admin; cuentas administrativas requieren super admin; editor queda limitado a contenido editorial.
- Mass assignment abierto: eliminados todos los `$guarded = []` y reemplazados por `$fillable` explícito. Campos de seguridad de User se asignan exclusivamente con `forceFill` en flujos autorizados.

### Medios

- Turnstile incompleto/fail-open: ahora valida token obligatorio, action exacta, hostname, fallos de red, honeypot y tiempo; en producción falla cerrado si falta el secreto (configurable). Agregado a login, registro, contacto y cotización.
- Rate limiting parcial: agregados límites separados para registro, formularios públicos, búsqueda, uploads y checkout, combinando IP, identidad hasheada y usuario.
- Uploads: regla común rechaza extensiones ejecutables/dobles sospechosas, archivos inválidos e imágenes con exceso de píxeles; se mantienen allowlists MIME y tamaño por operación. ClamAV queda como integración operativa del hosting.
- Headers ausentes: middleware central con CSP Report-Only, HSTS opt-in sobre HTTPS, `nosniff`, referrer, permissions, framing y políticas cross-origin.
- `.env.example` inseguro para producción y `.htaccess` básico: corregidos valores guía, exclusiones Git, reglas Apache y plantillas cPanel con aplicación fuera de `public_html`.

### Bajos / confirmaciones

- No se encontró SQL concatenado con entrada de usuario. Los `whereRaw` usan bindings; `DB::raw` sólo contiene expresiones constantes en migraciones.
- CSRF web permanece activo y no hay exclusiones detectadas.
- No se encontraron secretos rastreados ni `.env` en el historial consultado. El `.env` y log locales están ignorados.
- `composer audit --locked`: sin advisories. `npm audit --omit=dev`: 0 vulnerabilidades.

## Pruebas y comandos ejecutados

- `php artisan test`: 21 pruebas, 119 aserciones, todas aprobadas.
- `npm run build`: aprobado. Advertencia no bloqueante por chunks grandes.
- `php artisan route:list --except-vendor`: aprobado.
- PHP lint en cada archivo PHP modificado: aprobado.
- `git diff --check`: aprobado.

## Riesgos residuales

- Los archivos privados heredados siguen accesibles en el disco público hasta migrarlos al disco local y retirar sus copias públicas.
- CSP continúa Report-Only y conserva `unsafe-inline` por scripts/estilos inline existentes. Debe observarse en staging, migrar inline a nonce/hash/archivos y luego activar enforcement.
- 2FA administrativo, ClamAV, WAF, backups externos, restricción del origen Cloudflare, alertas y rotación real dependen de cPanel/proveedor y deben completarse con el checklist operativo.
- No se ejecutaron pruebas agresivas DAST, malware real ni pentest externo; deben hacerse sólo en staging autorizado.
- La invalidez de “otras sesiones” al cambiar contraseña requiere una interfaz de cambio de contraseña dedicada o un session store que permita revocación por usuario.

## Archivos y entregables

- Middleware: `SecurityHeaders`, `EnsureAdminLevel` y refuerzo de `EnsureAdminUser`.
- Policies: `ClientOrderPolicy`, `ClientPriceListPolicy`.
- Servicios/reglas: `HtmlSanitizer`, `TurnstileService`, `SecureUpload`.
- Configuración: `config/security.php`, `.env.example`, `.gitignore`, `public/.htaccess`.
- cPanel: plantillas en `deployment/cpanel/public_html`.
- Operación: `docs/SECURITY_PRODUCTION.md` (cPanel, Cloudflare, despliegue, rollback, incidentes, rotación y backups).
- Pruebas: `SecurityHardeningTest`, `HtmlSanitizerTest`.
- Migraciones nuevas: ninguna; los cambios son compatibles con el esquema existente.

## Rollback resumido

Antes del despliegue, backup de DB y release. Ante fallo: mantenimiento, detener workers, restaurar release anterior, restaurar DB sólo si hubo cambio incompatible (esta entrega no agrega migraciones), limpiar/regenerar caches, reiniciar workers, smoke tests y reabrir. El procedimiento detallado está en `SECURITY_PRODUCTION.md`.

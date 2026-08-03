# Seguridad y producción

Este documento complementa las protecciones internas. No sustituye controles del hosting, actualizaciones ni monitoreo.

## Estructura cPanel

Preferir que el Document Root apunte a `/home/USUARIO/aplicacion/public`. Si no es posible, mantener la aplicación en `/home/USUARIO/aplicacion` y copiar a `public_html` solamente el contenido público compilado, `.htaccess` e `index.php`. Usar las plantillas de `deployment/cpanel/public_html` y ajustar `$applicationPath`. Nunca copiar `.env`, `vendor`, `storage`, logs, fuentes, source maps, backups, SQL o ZIP a `public_html`.

Permisos: directorios `0755`, archivos `0644`, `.env` `0600` o `0640`; conceder escritura sólo a `storage` y `bootstrap/cache` para el usuario de PHP-FPM. Nunca usar `777`.

En cPanel activar AutoSSL, HTTPS, PHP-FPM, ModSecurity/OWASP CRS, 2FA del panel, escáner de malware y backups. No desactivar ModSecurity globalmente: documentar el ID del falso positivo y solicitar una excepción mínima por ruta/regla. PHP debe usar `display_errors=Off`, `log_errors=On`, `expose_php=Off`, `allow_url_include=Off`.

## Cloudflare

Usar proxy activo, SSL Full (Strict), Always Use HTTPS, reglas administradas WAF y challenge/rate limit para `/admin/login`, `/clientes/registro`, `/contacto`, `/presupuesto` y scanners. Restringir acceso directo al origen cuando el hosting lo permita. Excluir de caché `/admin/*`, `/clientes/*`, `/analytics/events`, cookies de sesión, carrito, CSRF y respuestas personalizadas. Configurar widgets Turnstile separados con actions `login`, `registration`, `contact` y `quote`; definir hostname exacto.

La CSP inicia en Report-Only (`SECURITY_CSP_ENFORCE=false`). Revisar consola/reportes en staging con Vite, fuentes, MapLibre, PostHog, Turnstile, YouTube y WebSockets. Corregir recursos legítimos, eliminar dependencias innecesarias y recién entonces activar enforcement. `unsafe-eval` no está permitido; la eliminación de `unsafe-inline` requiere migrar los scripts inline existentes a archivos con nonce/hash.

## Despliegue repetible

```bash
php artisan down --render="errors::503"
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan queue:restart
php artisan up
php artisan about --only=environment
```

Compilar fuera del servidor si cPanel no ofrece una versión Node compatible. Antes de `config:cache`, ejecutar `rg 'env\(' app routes resources`; `env()` debe existir sólo en archivos `config`. Crear backup previo y smoke tests de home, login, contacto, descarga autorizada, 403/404/419/429/500/503 y headers.

Cron cada minuto: `* * * * * /usr/local/bin/php /home/USUARIO/aplicacion/artisan schedule:run >> /home/USUARIO/logs/scheduler.log 2>&1`. Mantener logs fuera de `public_html`. Programar limpieza de sesiones/tokens/uploads temporales, retención de eventos y backups.

## Backups y restauración

Diario: base MySQL y uploads privados; semanal: aplicación/configuración necesaria; retención sugerida 7 diarios, 5 semanales y 12 mensuales. Mantener una copia cifrada fuera del servidor y proteger por una clave separada. Cada trimestre restaurar en staging: verificar checksum, importar DB con usuario de migración, restaurar archivos privados, configurar secretos nuevos, ejecutar migraciones y smoke tests. Registrar fecha, responsable, duración y resultado.

## Rollback

Conservar release anterior y backup de DB. Activar mantenimiento, detener workers, restaurar código anterior, ejecutar sólo una migración `down` previamente probada cuando sea reversible (si no, restaurar DB), regenerar caches, reiniciar workers, probar y desactivar mantenimiento. Nunca improvisar rollback destructivo sobre producción.

## Incidentes

1. Aislar: mantenimiento/WAF, revocar sesiones y bloquear el indicador concreto.
2. Preservar evidencia: logs, timestamps, hashes y copia de DB/disco; no alterar originales.
3. Contener: rotar credenciales, tokens, `APP_KEY` sólo con plan de impacto, SMTP, DB, Cloudflare y cPanel; invalidar sesiones.
4. Erradicar: parchear causa, revisar persistencia/web shells, dependencias y cuentas.
5. Recuperar desde backup verificado, monitorear y comunicar según obligaciones legales/contractuales.
6. Documentar cronología, alcance, datos afectados y acciones preventivas.

## Rotación de credenciales

Crear el secreto nuevo en el proveedor, desplegarlo por `.env`/gestor del hosting sin registrarlo en Git, limpiar `config:cache`, verificar, revocar el anterior y revisar logs. Si alguna clave apareció en Git, considerarla comprometida y rotarla aunque se borre del último commit; limpiar historial sólo como medida adicional coordinada. Para MySQL usar usuario exclusivo sin privilegios globales, `FILE`, `SUPER`, `PROCESS`, `GRANT`, `CREATE USER` ni `DROP DATABASE`; separar usuario de migración si es viable.

## Operación

Ejecutar periódicamente `composer audit` y `npm audit`; revisar cada alerta, sin `npm audit fix --force`. Producción usa locks y no instala dependencias dev. Configurar rotación/retención de logs; jamás registrar contraseñas, tokens, cookies, Authorization o documentos completos. Integrar ClamAV del hosting mediante cola antes de publicar archivos si está disponible.

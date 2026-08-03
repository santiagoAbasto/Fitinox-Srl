# Arquitectura de Analítica Web

## Alcance

La analítica propia es la fuente operativa del panel. PostHog complementa con grabaciones, mapas de clics y funnels cuando se configura. Cloudflare aporta protección perimetral y sus métricas se integrarán únicamente mediante credenciales de lectura.

## Flujo

1. El tracker público crea identificadores anónimos de visitante y sesión.
2. Los eventos permitidos se envían en lotes a `POST /analytics/events` con CSRF e idempotencia.
3. Laravel resuelve la IP en servidor, almacena únicamente hash y versión en eventos normales, clasifica bots y aplica límites.
4. Los eventos se guardan normalizados; el comando `analytics:summarize` genera agregados diarios.
5. El dashboard consulta agregados y ventanas acotadas, nunca entrega IP completa a React.

## Tablas

- `analytics_sessions`: sesión, visitante anónimo, adquisición, dispositivo y geografía aproximada.
- `analytics_page_views`: navegación, tiempos, respuesta y Web Vitals.
- `analytics_section_views`: exposición real de secciones y transición entre secciones.
- `analytics_events`: eventos permitidos e idempotentes.
- `analytics_conversions`: resultados comerciales vinculados a sesión.
- `analytics_daily_summaries`: agregados compactos por fecha y dimensión.
- `analytics_ip_reputation`: hash de IP, clasificación y bloqueos; acceso ampliado auditado.
- `analytics_blocked_requests` y `analytics_bot_events`: evidencia de protección.
- `analytics_security_audits`: acciones sensibles de seguridad.

## Privacidad

No se almacenan contraseñas, tokens, cookies, cabeceras de autorización ni contenido de campos. Las propiedades se filtran por lista permitida y tamaño. Las rutas administrativas se excluyen del tracker y de PostHog por defecto. La ubicación es aproximada a país/ciudad y nunca representa una ubicación personal exacta.

## Escalabilidad

Los índices cubren sesión, visitante, fecha, ruta, evento, sección, país, bot y conversión. La ingestión acepta lotes pequeños, responde sin consultas analíticas y puede migrarse a un job en cola manteniendo el contrato. Los resúmenes diarios se cachean y la retención se ejecuta por comando programado.

# Cambios v2 (fases 1 y 2: seguridad + base de datos)

## Orden de despliegue (IMPORTANTE)
1. Copia de seguridad de la BD remota.
2. Ejecutar `Back/sql/migracion_v2.sql` UNA vez (opcional: `datos_demo_v2.sql` si tus coches id 1-5 son los del dump original).
3. `php Back/tools/hash_admin_passwords.php` (con las variables DB_* del entorno o un .env local)
   y después `php Back/tools/hash_admin_passwords.php admin` para poner una contraseña nueva.
4. Desplegar el código (Render reconstruye con el Dockerfile).

## Seguridad
- `.env` fuera de Git (`git rm --cached .env`), `.env.example`, `.dockerignore`. Rotar DB_PASS y JWT_SECRET.
- Los scripts de `Back/inc/*` ya no son accesibles por web; todo pasa por `admin/escritorio.php`
  (accion = crear | actualizar | eliminar) y exige sesión de administrador (`inc/auth.php`).
- Consultas preparadas en crear/actualizar (había inyección SQL en `fecha_ingreso`).
- Contraseñas de admin con hash; `session_regenerate_id`; límite de 5 intentos / 15 min (admin, cliente y API).
- CSRF en login, registro, perfil y agendar prueba. Logout destruye la cookie.
- JS del admin movido a `Back/js/escritorio.js` (la CSP bloqueaba los scripts/onclick inline).
- API: puerto de BD respetado, sin CORS `*`, JWT en base64url con comprobación de algoritmo.
- PHP/Apache endurecidos (`docker/php-hardening.ini`, ServerTokens Prod).

## Base de datos
Ver cabecera de `migracion_v2.sql`. `Back/sql/bbd.sql` es el esquema completo v2 para instalaciones nuevas
(contraseñas iniciales admin123 / baac123 YA hasheadas: cámbialas).

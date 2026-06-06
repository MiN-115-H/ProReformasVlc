# Despliegue en Render (Laravel) + Vercel (Vue)

Esta guia deja el backend en Render y el frontend SPA en Vercel.

## 1) Archivos ya preparados en el proyecto

- render.yaml: define el servicio web de Laravel en Render.
- vercel.json: build de Vite y rewrites de /api, /auth, /csrf-token y /storage al backend.
- vite.frontend.config.js: build dedicado para frontend SPA.
- index.html: entrada SPA para Vercel.
- resources/js/utils/csrf.js: obtiene CSRF para login/logout/panel en modo frontend separado.
- routes/web.php: nuevo endpoint GET /csrf-token.

## 2) Desplegar backend en Render

1. Sube el repositorio a GitHub.
2. En Render, crea un Web Service desde ese repo.
3. En la pantalla de runtime/language, selecciona Docker.
4. Render detectara render.yaml automaticamente (env: docker + Dockerfile).
5. En Render, define estas variables de entorno (Environment):

- APP_KEY: generar una clave de Laravel (base64:...)
- APP_URL: URL publica de Render, por ejemplo https://tu-backend.onrender.com
- APP_ENV=production
- APP_DEBUG=false
- LOG_CHANNEL=stderr
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD (segun tu base de datos)
- SESSION_DRIVER=database
- CACHE_STORE=database
- QUEUE_CONNECTION=database
- FILESYSTEM_DISK=public
- RECAPTCHA_SECRET_KEY (si usas captcha en backend)

6. Ejecuta migraciones una vez desplegado:

- Opcion A: desde Shell de Render: php artisan migrate --force
- Opcion B: como comando manual en un deploy puntual.

7. Crea enlace de storage (si no existe):

- php artisan storage:link

Nota: si vas a subir imagenes/ficheros y quieres persistencia robusta, valora S3 en lugar de disco local.

## 3) Configurar frontend en Vercel

1. Crea proyecto en Vercel apuntando al mismo repo.
2. Framework preset: Vite.
3. Build command: npm run build:frontend.
4. Output directory: dist.
5. Abre vercel.json y reemplaza todas las apariciones de:

- https://YOUR-RENDER-BACKEND.onrender.com

por tu URL real de Render.

6. Redeploy en Vercel.

## 4) Variables recomendadas en Vercel

Si usas captcha en frontend:

- VITE_RECAPTCHA_SITE_KEY=tu_site_key

(Se configura en Project Settings -> Environment Variables)

## 5) Checklist de verificacion

- Frontend carga en Vercel sin 404 al refrescar rutas internas (/, /servicios, /admin/panel).
- GET /api/servicios responde desde frontend.
- Login admin funciona en /login.
- Acciones POST/PATCH/DELETE del panel admin funcionan.
- Imagenes en /storage se ven correctamente.
- Formulario de contacto funciona y respeta rate limit.

## 6) Problemas tipicos y solucion

- Error 419 (CSRF):
  - Confirma que /csrf-token esta accesible desde Vercel (rewrite correcto).
  - Verifica que login/logout pasan por /auth hacia Render.

- Error CORS:
  - Con rewrites de Vercel no deberia aparecer (navegador ve mismo origen).
  - Si llamas directo a Render desde navegador, deberas configurar CORS en Laravel.

- 404 al recargar en una ruta SPA:
  - Verifica el rewrite final a /index.html en vercel.json.

- No aparecen archivos subidos:
  - Revisa storage:link y estrategia de persistencia (ideal: S3).

# Lista de Verificación para el Despliegue en Producción
**Portal de Gestión de Dirección de Escuela — Módulo Bienestar Universitario (UNT)**

---

### 1. Variables de Entorno y Configuración del Sistema (`.env`)

- [ ] **`APP_ENV=production`**: Garantiza que el entorno no exponga herramientas de depuración ni accesos rápidos de desarrollo en el inicio de sesión. Además, activa la protección automática de `./reiniciar-demo.sh` para impedir reinicios accidentales.
- [ ] **`APP_DEBUG=false`**: Impide que se muestren trazas de error o detalles internos del servidor/base de datos ante errores no controlados.
- [ ] **`APP_KEY`**: Generar una clave criptográfica de 32 bytes única para producción (`php artisan key:generate`).
  > [!CAUTION]
  > **Crítico**: La pérdida de `APP_KEY` imposibilitará descifrar la descripción de casos confidenciales, los motivos de citas y las notas de evolución clínica de los especialistas. Debe ser respaldada en el gestor de secretos institucional.
- [ ] **`APP_URL=https://bienestar.unt.edu.pe`**: Configurar el dominio canónico institucional con esquema `https://`.
- [ ] **`SESSION_SECURE_COOKIE=true`**: Asegurar que las cookies de sesión y CSRF solo se transmitan bajo conexiones HTTPS cifradas.

---

### 2. Base de Datos y Seeders de Producción

- [ ] **Ejecución de migraciones en modo seguro**:
  ```bash
  php artisan migrate --force
  ```
- [ ] **Carga exclusiva del seeder de producción (`BienestarProduccionSeeder`)**:
  Carga únicamente los catálogos institucionales y la cuenta del Administrador inicial. Rechaza por validación cualquier clave por defecto (`password123`):
  ```bash
  INITIAL_ADMIN_EMAIL="direccion.sistemas@unitru.edu.pe" INITIAL_ADMIN_PASSWORD="ClaveSeguraProduccion2026!#" php artisan db:seed --class="App\Modules\Bienestar\Database\Seeders\BienestarProduccionSeeder" --force
  ```
  > [!IMPORTANT]
  > **Orden de ejecución crítico**: `BienestarProduccionSeeder` DEBE ejecutarse **ANTES** de ejecutar `php artisan config:cache`. En Laravel, una vez que la configuración se cachea, las llamadas directas a `env()` devuelven `null`, lo que impediría leer `INITIAL_ADMIN_PASSWORD`.

- [ ] **Política de copias de seguridad (Backups)**:
  Configurar un cron job diario con `pg_dump` para el esquema `bienestar`:
  ```bash
  pg_dump -U bienestar_user -d portal_unt -n bienestar -F c -f "/backup/bienestar_$(date +\%Y\%m\%d).dump"
  ```

---

### 3. Almacenamiento y Protección de Archivos

- [ ] **Aislamiento del disco privado**:
  Verificar que `storage/app/private/documentos_apoyo` no se ubique en la raíz pública del servidor web ni sea accesible mediante enlaces estáticos. La descarga solo se realiza a través de la ruta protegida por la Policy `DocumentoAdjuntoPolicy`.
- [ ] **Permisos de lectura/escritura**:
  Asegurar permisos `775` asignados exclusivamente al usuario del servidor web (`www-data`):
  ```bash
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R 775 storage bootstrap/cache
  ```

---

### 4. Seguridad de Red y Terminación HTTPS

- [ ] **Terminación SSL/TLS (HTTPS)**:
  Certificado SSL/TLS institucional activo (TLS 1.2 / TLS 1.3).
- [ ] **Configuración de Proxy de Confianza**:
  Configurar proxies de confianza en Laravel para interpretar correctamente los encabezados `X-Forwarded-Proto` y `X-Forwarded-For`.
- [ ] **Encabezados HTTP de Seguridad**:
  Habilitar cabeceras institucionales en el servidor web Nginx:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`

---

### 5. Optimización de Rendimiento y Caché

- [ ] **Caché de configuración, rutas y vistas**:
  *(Ejecutar únicamente después de haber ejecutado las migraciones y seeders)*
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] **Optimización del autoloader de Composer**:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

# Lista de Verificación para el Despliegue en Producción
**Portal de Gestión de Dirección de Escuela — Módulo Bienestar Universitario (UNT)**

---

### 1. Variables de Entorno y Configuración del Sistema (`.env`)

- [ ] **`APP_ENV=production`**: Garantiza que el entorno no exponga herramientas de depuración ni accesos rápidos de desarrollo.
- [ ] **`APP_DEBUG=false`**: Impide que se muestren stack traces o información interna del servidor/base de datos ante errores no controlados.
- [ ] **`APP_KEY`**: Generar una clave criptográfica de 32 bytes única para producción (`php artisan key:generate`). **Importante**: La pérdida de esta clave impide descifrar las notas de seguimiento y motivos de citas existentes.
- [ ] **`APP_URL=https://bienestar.unt.edu.pe`**: Configurar el dominio canónico con esquema `https://`.
- [ ] **`SESSION_SECURE_COOKIE=true`**: Forzar que las cookies de sesión y CSRF solo se transmitan por conexiones HTTPS seguras.

---

### 2. Base de Datos y Seeders de Producción

- [ ] **Ejecución de migraciones en modo seguro**:
  ```bash
  php artisan migrate --force
  ```
- [ ] **Carga exclusiva del seeder de producción**:
  Ejecutar el seeder institucional limpio (solo catálogos y cuenta inicial de Administrador; sin datos simulados de estudiantes, casos ni citas):
  ```bash
  INITIAL_ADMIN_PASSWORD="TuContraseñaSegura123!" php artisan db:seed --class="App\Modules\Bienestar\Database\Seeders\BienestarProduccionSeeder" --force
  ```
  > [!IMPORTANT]
  > **Orden de ejecución crítico**: `BienestarProduccionSeeder` DEBE ejecutarse **ANTES** de ejecutar `php artisan config:cache`. En Laravel, una vez que la configuración se cachea, las llamadas directas a `env()` devuelven `null`, lo que impediría leer `INITIAL_ADMIN_PASSWORD`.

- [ ] **Cambio inmediato de credencial administrativa**:
  Ingresar con el usuario administrador inicial y actualizar la contraseña por una de alta entropía.
- [ ] **Política de copias de seguridad (Backups)**:
  Configurar un cron job diario con `pg_dump` para el esquema `bienestar`:
  ```bash
  pg_dump -U bienestar_user -d portal_unt -n bienestar -F c -f "/backup/bienestar_$(date +\%Y\%m\%d).dump"
  ```

---

### 3. Almacenamiento y Protección de Archivos

- [ ] **Aislamiento del disco privado**:
  Verificar que `storage/app/private/documentos_apoyo` no esté en la raíz pública del servidor web ni expuesto a través de URLs directas.
- [ ] **Permisos de lectura/escritura**:
  Asegurar permisos `750` para el usuario del servidor web (`www-data`):
  ```bash
  chown -R www-data:www-data storage bootstrap/cache
  chmod -R 775 storage bootstrap/cache
  ```

---

### 4. Seguridad de Red y Terminación HTTPS

- [ ] **Terminación SSL/TLS (HTTPS)**:
  Certificado válido emitido para el dominio institucional con TLS 1.3 / 1.2.
- [ ] **Configuración de Proxy de Confianza**:
  Configurar `TrustProxies` en Laravel para interpretar correctamente encabezados `X-Forwarded-Proto` y `X-Forwarded-For` de balanceadores de carga institucionales o Nginx reverse proxy.
- [ ] **Encabezados HTTP de Seguridad**:
  Habilitar `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN` y `Referrer-Policy: strict-origin-when-cross-origin`.

---

### 5. Optimización de Rendimiento y Caché

- [ ] **Caché de configuración, rutas y vistas**:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] **Optimización del autoloader de Composer**:
  ```bash
  composer install --no-dev --optimize-autoloader
  ```

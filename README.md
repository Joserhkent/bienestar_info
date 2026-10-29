# Portal de Gestión de Dirección de Escuela — Módulo de Bienestar Estudiantil
Universidad Nacional de Trujillo — Ingeniería de Sistemas / Informática

Sistema integral de gestión de Bienestar Universitario: atención y registro de casos, evaluación y dictamen de solicitudes de apoyo socioeconómico con resguardo documental privado, reserva y agendamiento de citas, y derivación a profesionales con sesiones de seguimiento clínico cifradas.

---

## 1. Cómo Levantar el Proyecto (Despliegue Local)

El sistema se ejecuta en contenedores Docker mediante `docker-compose.yml`:
- **Nginx (Servidor Web)**: `http://localhost:8080/bienestar`
- **PHP 8.3 FPM (Aplicación)**: Laravel 12
- **PostgreSQL 16 (Base de Datos)**: Esquema dedicado `bienestar` en la base de datos `portal_unt` (puerto del host `5433`, interno `5432`).

### Paso 1: Levantar los contenedores
```bash
docker compose up -d
```
*(Si su usuario no pertenece al grupo docker, ejecute con `sudo docker compose up -d`)*

### Paso 2: Base de datos de pruebas (si el volumen Docker ya existía)
Si el contenedor de PostgreSQL ya tenía un volumen previo, asegure la existencia de la base de datos de pruebas `portal_unt_test`:
```bash
docker compose exec postgres psql -U bienestar_user -d portal_unt -c "SELECT 'CREATE DATABASE portal_unt_test OWNER bienestar_user' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'portal_unt_test')\gexec"
docker compose exec postgres psql -U bienestar_user -d portal_unt_test -c "CREATE SCHEMA IF NOT EXISTS bienestar;"
```

### Paso 3: Inicializar la base de datos con los datos de demostración
```bash
./reiniciar-demo.sh
```
El script ejecuta las migraciones, seeders completos con expedientes de prueba y documentos PDF adjuntos reales en el disco privado, y asigna permisos estrictos (`www-data:www-data`, `775`) a `storage/` y `bootstrap/cache/`.

> [!CAUTION]
> **Advertencia Crítica sobre `APP_KEY`**:
> El módulo implementa cifrado simétrico AES-256-CBC mediante el cast `encrypted` de Laravel para proteger datos confidenciales y clínicos:
> - `caso_bienestar.descripcion`
> - `seguimiento.nota_avance`
> - `seguimiento.acuerdos`
> - `cita.motivo_consulta`
>
> Si la clave `APP_KEY` del archivo `.env` se regenera, modifica o pierde, **todos los registros clínicos y notas sensibles existentes no podrán descifrarse** (arrojando una excepción `DecryptException`). Respalde siempre la clave de cifrado junto con las copias de seguridad de la base de datos.

---

## 2. Cuentas de Acceso para Demostración

Todas las cuentas de prueba comparten la misma contraseña institucional de desarrollo:
> **Contraseña:** `password123`

| Rol | Correo Electrónico | Descripción / Perfil |
| :--- | :--- | :--- |
| **Administrador** | `admin@unitru.edu.pe` | Dirección de Escuela: supervisión de solo lectura en casos, solicitudes y citas, y generación/exportación de reportes institucionales. |
| **Personal de Bienestar** | `bienestar@unitru.edu.pe` | Registro y gestión de casos, dictamen de solicitudes socioeconómicas y derivación a profesionales. |
| **Especialista (Psicología)** | `psicologia@unitru.edu.pe` | Lic. Silvia Marina Vargas Rojas. Gestión de horarios, confirmación y atención de citas, notas clínicas y cierre de casos. |
| **Especialista (Tutoría)** | `tutor@unitru.edu.pe` | Mg. Jorge Luis Alva Mantilla. Tutoría académica, atención a estudiantes y seguimiento psicopedagógico. |
| **Docente** | `docente@unitru.edu.pe` | Perfil docente: navegación informativa (rutas confidenciales de casos y expedientes médicos devuelven `403 Forbidden`). |
| **Estudiante 1** | `estudiante1@unitru.edu.pe` | Carlos Mendoza. Solicitudes de apoyo alimentario y agendamiento de citas. |
| **Estudiante 2** | `estudiante2@unitru.edu.pe` | Andrea Paredes. Estudiante con condición TEA y derivación especializada activa. |
| **Estudiante 3** | `estudiante3@unitru.edu.pe` | Kevin Quispe. Solicitudes y trámites estudiantiles. |

---

## 3. Ejecución de Pruebas Automatizadas

El proyecto utiliza una base de datos de pruebas aislada (`portal_unt_test`) definida en `phpunit.xml`. Las pruebas no alteran ni borran los datos del entorno de desarrollo.

```bash
# Ejecutar la suite completa (60 tests, 1577+ aserciones)
docker compose exec -T app php artisan test

# O filtrar por suite específica:
docker compose exec -T app php artisan test --filter=P1CasosTest
docker compose exec -T app php artisan test --filter=P2SolicitudesTest
docker compose exec -T app php artisan test --filter=P3CitasTest
docker compose exec -T app php artisan test --filter=P4DerivacionesTest
docker compose exec -T app php artisan test --filter=P5NotificacionesReportesTest
docker compose exec -T app php artisan test --filter=RolMenuAccessTest
docker compose exec -T app php artisan test --filter=RolNavegacionCompletaTest
docker compose exec -T app php artisan test --filter=ProduccionSeederTest
docker compose exec -T app php artisan test --filter=BienestarSecurityTest
```

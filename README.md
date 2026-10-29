# Portal de Gestión de Dirección de Escuela — Módulo de Bienestar Estudiantil
Universidad Nacional de Trujillo — Ingeniería de Sistemas / Informática

Sistema integral de gestión de Bienestar Universitario: atención y registro de casos, evaluación y dictamen de solicitudes de apoyo socioeconómico con resguardo documental privado, reserva y agendamiento de citas, y derivación a profesionales con sesiones de seguimiento clínico cifradas.

---

## 1. Cómo Levantar el Proyecto (Despliegue Local)

El sistema se ejecuta en contenedores Docker mediante `docker-compose.yml`:
- **Nginx (Webserver)**: `http://localhost:8080/bienestar`
- **PHP 8.3 FPM (App)**: Laravel 12
- **PostgreSQL 16 (DB)**: Esquema dedicado `bienestar` en la base de datos `portal_unt` (puerto host `5433`, interno `5432`).

### Paso 1: Levantar los contenedores
```bash
docker compose up -d
```

### Paso 2: Base de datos de pruebas (si el volumen Docker ya existía)
Si el contenedor de base de datos ya tenía un volumen previo, asegure la existencia de la base de datos de pruebas `portal_unt_test`:
```bash
docker compose exec postgres psql -U bienestar_user -d portal_unt -c "SELECT 'CREATE DATABASE portal_unt_test OWNER bienestar_user' WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'portal_unt_test')\gexec"
docker compose exec postgres psql -U bienestar_user -d portal_unt_test -c "CREATE SCHEMA IF NOT EXISTS bienestar;"
```

### Paso 3: Inicializar la base de datos con los datos de demostración
```bash
./reiniciar-demo.sh
```

---

## 2. Cuentas de Acceso para Demostración

Todas las cuentas de prueba comparten la misma contraseña:
> **Contraseña:** `password123`

| Rol | Correo Electrónico | Descripción / Perfil |
| :--- | :--- | :--- |
| **Administrador** | `admin@unitru.edu.pe` | Gestión global del sistema, generación y exportación de reportes institucionales. |
| **Personal de Bienestar** | `bienestar@unitru.edu.pe` | Registro de casos, dictamen de solicitudes socioeconómicas y derivación a especialistas. |
| **Especialista (Psicología)** | `psicologia@unitru.edu.pe` | Lic. Silvia Marina Vargas Rojas. Confirmación de citas, derivaciones y notas de evolución clínica. |
| **Especialista (Tutoría)** | `tutor@unitru.edu.pe` | Mg. Jorge Luis Alva Mantilla. Tutoría universitaria y atención a estudiantes en riesgo. |
| **Docente** | `docente@unitru.edu.pe` | Vista docente (acceso restringido: `403` en casos y expedientes médicos confidenciales). |
| **Estudiante 1** | `estudiante1@unitru.edu.pe` | Carlos Mendoza. Solicitudes de comedor, agendamiento de citas. |
| **Estudiante 2** | `estudiante2@unitru.edu.pe` | Andrea Paredes. Estudiante con condición TEA y expediente en seguimiento. |
| **Estudiante 3** | `estudiante3@unitru.edu.pe` | Kevin Quispe. Solicitudes y trámites de bienestar. |

---

## 3. Ejecución de Pruebas Automatizadas

El proyecto cuenta con una base de datos de pruebas independiente (`portal_unt_test`) configurada en `phpunit.xml` para garantizar que la ejecución de pruebas no altere los datos de desarrollo.

```bash
# Ejecutar toda la suite de pruebas (54 tests)
docker compose exec app php artisan test

# Ejecutar por proceso o verificación de roles
docker compose exec app php artisan test --filter=P1CasosTest
docker compose exec app php artisan test --filter=P2SolicitudesTest
docker compose exec app php artisan test --filter=P3CitasTest
docker compose exec app php artisan test --filter=P4DerivacionesTest
docker compose exec app php artisan test --filter=P5NotificacionesReportesTest
docker compose exec app php artisan test --filter=RolMenuAccessTest
```

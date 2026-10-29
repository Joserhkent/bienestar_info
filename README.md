# Portal de Gestión de Dirección de Escuela — Módulo de Bienestar Estudiantil
Universidad Nacional de Trujillo — Ingeniería de Sistemas / Informática

Sistema integral de gestión de Bienestar Universitario: atención y registro de casos, evaluación y dictamen de solicitudes de apoyo socioeconómico con resguardo documental privado, reserva y agendamiento de citas, y derivación a profesionales con sesiones de seguimiento clínico cifradas.

---

## 1. Requisitos y Despliegue con Docker

El sistema se ejecuta en contenedores Docker mediante `docker-compose.yml`:
- **Nginx (Webserver)**: `http://localhost:8080/bienestar`
- **PHP 8.3 FPM (App)**: Laravel 12
- **PostgreSQL 16 (DB)**: Esquema dedicado `bienestar` en la base de datos `portal_unt` (puerto host `5433`, interno `5432`).

Para levantar los contenedores:
```bash
docker compose up -d
```

---

## 2. Reinicio de Entorno de Demostración (`reiniciar-demo.sh`)

Para restablecer la base de datos de desarrollo a su estado inicial limpio con todos los catálogos y datos de prueba del seeder:

```bash
./reiniciar-demo.sh
```

El script ejecuta automáticamente `php artisan migrate:fresh --seed`, sincroniza las secuencias de claves primarias en PostgreSQL y verifica los enlaces de almacenamiento.

---

## 3. Cuentas de Acceso para Demostración

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

## 4. Ejecución de Pruebas Automatizadas

El proyecto cuenta con una base de datos de pruebas independiente (`portal_unt_test`) configurada en `phpunit.xml` para garantizar que la ejecución de pruebas no altere los datos de desarrollo.

```bash
# Ejecutar toda la suite de pruebas
docker compose exec app php artisan test

# Ejecutar por proceso
docker compose exec app php artisan test --filter=P1CasosTest
docker compose exec app php artisan test --filter=P2SolicitudesTest
docker compose exec app php artisan test --filter=P3CitasTest
docker compose exec app php artisan test --filter=P4DerivacionesTest
```

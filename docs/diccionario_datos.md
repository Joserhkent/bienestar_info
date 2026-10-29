# DICCIONARIO DE DATOS — MÓDULO BIENESTAR ESTUDIANTIL
**Proyecto:** Portal de Gestión de Dirección de Escuela — UNT  
**Asignatura:** Proyecto de Competencia — Dr. Mendoza Torres Edwin  
**Grupo 11:** Módulo de Gestión de Bienestar Estudiantil (`BienestarTutoriaModule`)  
**Motor de Base de Datos:** PostgreSQL 15+ / 16+  
**Esquema:** `bienestar`  

---

## 1. Tabla: `rol`
Almacena los roles del sistema para control de acceso y autorización.
- **Relación:** R01 con `usuario` (1:N).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_rol` | `SERIAL` | NO | PK | Auto | Identificador único del rol |
| `nombre` | `VARCHAR(50)` | NO | UNIQUE | - | Nombre (Estudiante, Personal Bienestar, Especialista, Docente, Administrador) |
| `descripcion` | `VARCHAR(255)` | SÍ | - | - | Alcance y descripción de privilegios del rol |

---

## 2. Tabla: `usuario`
Credenciales de autenticación, sesión y estado del usuario.
- **Relación:** R01 hacia `rol`. Identifica a `estudiante` (R02, 1:1) y `especialista` (R03, 1:1).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_usuario` | `SERIAL` | NO | PK | Auto | Identificador único de usuario |
| `id_rol` | `INT` | NO | FK | - | Referencia a `bienestar.rol(id_rol)` |
| `correo` | `VARCHAR(100)` | NO | UNIQUE | - | Correo institucional UNT |
| `contrasena_hash` | `VARCHAR(255)` | NO | - | - | Hash bcrypt/Argon2id de la contraseña |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'activo' | Estado: `activo`, `inactivo`, `bloqueado` |
| `fecha_creacion` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de creación del registro |
| `ultimo_acceso` | `TIMESTAMP` | SÍ | - | NULL | Registro de última conexión |

---

## 3. Tabla: `estudiante`
Datos generales del estudiante matriculado en Informática.
- **Relación:** R02 hacia `usuario` (1:1 estricto con `id_usuario UNIQUE`).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_estudiante` | `SERIAL` | NO | PK | Auto | Identificador del estudiante |
| `id_usuario` | `INT` | NO | FK, UNIQUE | - | Cuenta de acceso asociada |
| `codigo_universitario` | `VARCHAR(10)` | NO | UNIQUE | - | Código UNT (ej. `1021400123`) |
| `dni` | `VARCHAR(8)` | NO | UNIQUE | - | Documento Nacional de Identidad |
| `nombres` | `VARCHAR(80)` | NO | - | - | Nombres del estudiante |
| `apellidos` | `VARCHAR(80)` | NO | - | - | Apellidos paterno y materno |
| `sexo` | `CHAR(1)` | NO | CHECK | - | `M`, `F`, `O` |
| `fecha_nacimiento` | `DATE` | NO | - | - | Fecha de nacimiento |
| `telefono` | `VARCHAR(15)` | SÍ | - | NULL | Teléfono móvil o de contacto |
| `direccion_actual` | `VARCHAR(255)` | SÍ | - | NULL | Dirección en Trujillo |
| `zona_procedencia` | `VARCHAR(50)` | NO | CHECK | - | `costa`, `sierra`, `selva`, `otra` |
| `lugar_procedencia` | `VARCHAR(100)` | NO | - | - | Ciudad/distrito natal (ej. Otuzco, Huamachuco) |
| `ciclo_actual` | `INT` | NO | CHECK | - | Ciclo académico (1 al 10) |
| `fecha_registro` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de registro |

---

## 4. Tabla: `especialista`
Personal profesional de apoyo (Psicólogos, Tutores, Asistentas Sociales).
- **Relación:** R03 hacia `usuario` (1:1 estricto con `id_usuario UNIQUE`).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_especialista` | `SERIAL` | NO | PK | Auto | Identificador del especialista |
| `id_usuario` | `INT` | NO | FK, UNIQUE | - | Cuenta de acceso del especialista |
| `nombres` | `VARCHAR(80)` | NO | - | - | Nombres |
| `apellidos` | `VARCHAR(80)` | NO | - | - | Apellidos |
| `especialidad` | `VARCHAR(100)` | NO | - | - | Psicología Clínica, Trabajo Social, Tutoría |
| `nro_colegiatura` | `VARCHAR(20)` | NO | UNIQUE | - | Colegiatura profesional (ej. C.Ps.P.) |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'activo' | `activo`, `inactivo`, `licencia` |

---

## 5. Tabla: `perfil_socioeconomico`
Evaluación socioeconómica y de vulnerabilidad familiar (SISFOH).
- **Relación:** R04 hacia `estudiante` (1:1 con `id_estudiante UNIQUE`).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_perfil` | `SERIAL` | NO | PK | Auto | Identificador del perfil |
| `id_estudiante` | `INT` | NO | FK, UNIQUE | - | Referencia al estudiante evaluado |
| `clasificacion_sisfoh` | `VARCHAR(25)` | NO | CHECK | - | `no_pobre`, `pobre`, `pobre_extremo` |
| `violencia_familiar` | `BOOLEAN` | NO | - | `FALSE` | Indicador confidencial de violencia doméstica |
| `situacion_orfandad` | `VARCHAR(20)` | NO | CHECK | 'ninguna' | `ninguna`, `padre`, `madre`, `ambos` |
| `es_sustento_familiar` | `BOOLEAN` | NO | - | `FALSE` | Si es el sustento económico principal |
| `nro_dependientes` | `INT` | NO | CHECK | 0 | Cantidad de personas a cargo |
| `observacion` | `TEXT` | SÍ | - | NULL | Anotaciones de la trabajadora social |
| `fecha_actualizacion`| `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Última actualización socioeconómica |

---

## 6. Tabla: `tipo_condicion`
Catálogo de discapacidades y condiciones de neurodivergencia.
- **Base Legal:** Ley 29973 (Discapacidad) y Ley 30150 (TEA).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_tipo_condicion` | `SERIAL` | NO | PK | Auto | Identificador de la condición |
| `nombre` | `VARCHAR(100)` | NO | UNIQUE | - | Ej: Trastorno del Espectro Autista, Hipoacusia |
| `categoria` | `VARCHAR(50)` | NO | CHECK | - | `fisica_movilidad`, `sensorial_visual`, `sensorial_auditiva`, `neurodivergencia_tea`, `otra` |
| `base_legal` | `VARCHAR(100)` | NO | - | - | Norma que ampara (ej. Ley 29973 / Ley 30150) |

---

## 7. Tabla: `deporte`
Catálogo de disciplinas deportivas para olimpiadas y selecciones UNT.

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_deporte` | `SERIAL` | NO | PK | Auto | Identificador del deporte |
| `nombre` | `VARCHAR(80)` | NO | UNIQUE | - | Fútbol, Futsal, Básquet, Ajedrez, Vóley |

---

## 8. Tabla: `tipo_caso`
Catálogo de tipologías de atención en Bienestar.

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_tipo_caso` | `SERIAL` | NO | PK | Auto | Identificador del tipo |
| `nombre` | `VARCHAR(100)` | NO | UNIQUE | - | Salud Mental, Socioeconómico, Académico |
| `descripcion` | `VARCHAR(255)` | SÍ | - | - | Detalle de la clasificación |

---

## 9. Tabla: `caso_bienestar`
Expediente del caso atendido por la Escuela (P1).
- **Relaciones:** R05 (`estudiante`), R06 (`tipo_caso`), R07 (`usuario` que registra).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_caso` | `SERIAL` | NO | PK | Auto | Número de expediente del caso |
| `id_estudiante` | `INT` | NO | FK | - | Estudiante afectado |
| `id_tipo_caso` | `INT` | NO | FK | - | Tipología del caso |
| `id_usuario_registro` | `INT` | NO | FK | - | Personal de bienestar que abre el caso |
| `descripcion` | `TEXT` | NO | - | - | Resumen detallado del caso |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'registrado' | `registrado`, `en_atencion`, `derivado`, `en_seguimiento`, `cerrado` |
| `prioridad` | `VARCHAR(15)` | NO | CHECK | 'media' | `baja`, `media`, `alta`, `urgente` |
| `confidencial` | `BOOLEAN` | NO | - | `TRUE` | Bandera de estricta confidencialidad UNT |
| `fecha_registro` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Apertura del caso |
| `fecha_cierre` | `TIMESTAMP` | SÍ | - | NULL | Conclusión formal del expediente |

---

## 10. Tabla: `historial_estado_caso`
Trazabilidad de auditoría para cada transición de estado en un caso.
- **Relaciones:** R08 (`caso_bienestar`), R09 (`usuario`).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_historial` | `SERIAL` | NO | PK | Auto | Identificador del log |
| `id_caso` | `INT` | NO | FK | - | Caso modificado |
| `id_usuario` | `INT` | NO | FK | - | Usuario responsable del cambio |
| `estado_anterior` | `VARCHAR(20)` | SÍ | - | NULL | Estado previo (NULL en creación) |
| `estado_nuevo` | `VARCHAR(20)` | NO | - | - | Nuevo estado alcanzado |
| `fecha_cambio` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Momento exacto de transición |
| `comentario` | `TEXT` | SÍ | - | NULL | Justificación del cambio de estado |

---

## 11. Tabla: `derivacion`
Remisión de casos hacia especialistas internos o a la DBU central (P4).
- **Relaciones:** R10 (`usuario_deriva`), R11 (`caso_bienestar`), R12 (`especialista`).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_derivacion` | `SERIAL` | NO | PK | Auto | Identificador de la derivación |
| `id_caso` | `INT` | NO | FK | - | Caso objeto de derivación |
| `id_especialista` | `INT` | NO | FK | - | Especialista receptor |
| `id_usuario_deriva` | `INT` | NO | FK | - | Personal que realiza la derivación |
| `motivo` | `TEXT` | NO | - | - | Motivo clínico o social de la derivación |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'pendiente' | `pendiente`, `en_atencion`, `concluida`, `rechazada` |
| `fecha_derivacion` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de emisión de la orden |

---

## 12. Tabla: `seguimiento`
Bitácora de sesiones y avance del tratamiento o tutoría (P4).
- **Relación:** R13 hacia `derivacion` (1:N).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_seguimiento` | `SERIAL` | NO | PK | Auto | Identificador de la sesión |
| `id_derivacion` | `INT` | NO | FK | - | Derivación asociada |
| `fecha_sesion` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha y hora en que se efectuó la sesión |
| `nota_avance` | `TEXT` | NO | - | - | Resumen del progreso observado |
| `acuerdos` | `TEXT` | SÍ | - | NULL | Compromisos estudiante-especialista |
| `proxima_fecha` | `TIMESTAMP` | SÍ | - | NULL | Próxima cita pactada |

---

## 13. Tabla: `solicitud_apoyo`
Trámite de beneficios estudiantiles (Comedor, Becas, Subsidios) (P2).
- **Relaciones:** R14 (`estudiante`), R15 (`usuario_revisa`, nullable).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_solicitud` | `SERIAL` | NO | PK | Auto | Código de solicitud |
| `id_estudiante` | `INT` | NO | FK | - | Estudiante solicitante |
| `id_usuario_revisa` | `INT` | SÍ | FK | NULL | Personal asignado para dictamen |
| `tipo_apoyo` | `VARCHAR(50)` | NO | CHECK | - | `comedor_universitario`, `beca_alimentaria`, etc. |
| `motivo` | `TEXT` | NO | - | - | Argumentación de la necesidad |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'enviada' | `enviada`, `en_revision`, `aprobada`, `rechazada` |
| `fecha_envio` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Registro de la solicitud |
| `fecha_resolucion` | `TIMESTAMP` | SÍ | - | NULL | Fecha en que se aprueba o rechaza |
| `observacion` | `TEXT` | SÍ | - | NULL | Justificación del dictamen final |

---

## 14. Tabla: `documento_adjunto`
Evidencias documentales adjuntas a una solicitud (P2).
- **Relación:** R16 hacia `solicitud_apoyo` (1:N).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_documento` | `SERIAL` | NO | PK | Auto | Identificador del archivo |
| `id_solicitud` | `INT` | NO | FK | - | Solicitud vinculada |
| `tipo_documento` | `VARCHAR(80)` | NO | - | - | Constancia SISFOH, Recibo Servicio, etc. |
| `nombre_archivo` | `VARCHAR(255)` | NO | - | - | Nombre original del archivo |
| `ruta_archivo` | `VARCHAR(500)` | NO | - | - | Ruta segura en almacenamiento local/S3 |
| `tamano_kb` | `INT` | NO | CHECK | - | Tamaño en kilobytes (> 0) |
| `fecha_subida` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de carga |

---

## 15. Tabla: `horario_disponible`
Bloques de agenda ofrecidos por especialistas para atención (P3).
- **Relación:** R17 hacia `especialista` (1:N).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_horario` | `SERIAL` | NO | PK | Auto | Identificador del bloque horario |
| `id_especialista` | `INT` | NO | FK | - | Especialista disponible |
| `fecha` | `DATE` | NO | - | - | Fecha de atención |
| `hora_inicio` | `TIME` | NO | - | - | Hora de inicio |
| `hora_fin` | `TIME` | NO | - | - | Hora de fin (`hora_fin > hora_inicio`) |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'disponible' | `disponible`, `reservado`, `bloqueado` |

---

## 16. Tabla: `cita`
Reserva de sesión de atención médica/psicológica/tutoría (P3).
- **Relaciones:** R18 (`horario_disponible`, UNIQUE), R19 (`estudiante`), R20 (`caso_bienestar`, nullable).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_cita` | `SERIAL` | NO | PK | Auto | Identificador de cita |
| `id_horario` | `INT` | NO | FK, UQ Parcial | - | Bloque horario reservado (Único donde `estado != 'cancelada'`) |
| `id_estudiante` | `INT` | NO | FK | - | Estudiante que solicita la cita |
| `id_caso` | `INT` | SÍ | FK | NULL | Caso de bienestar asociado (si aplica) |
| `tipo_cita` | `VARCHAR(30)` | NO | CHECK | - | `psicologia`, `tutoria_academica`, `apoyo_social`, `adaptada_tea` |
| `estado` | `VARCHAR(20)` | NO | CHECK | 'solicitada' | `solicitada`, `confirmada`, `realizada`, `cancelada` |
| `motivo` | `VARCHAR(255)` | NO | - | - | Motivo de la cita |
| `fecha_solicitud` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de creación |
| `fecha_confirmacion` | `TIMESTAMP` | SÍ | - | NULL | Momento en que especialista confirma |

---

## 17. Tabla: `notificacion`
Avisos dirigidos a usuarios del sistema sobre casos, solicitudes y citas (P5).
- **Relaciones:** R21 hacia `usuario`, R22 hacia `caso_bienestar` (nullable).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_notificacion` | `SERIAL` | NO | PK | Auto | Identificador de notificación |
| `id_usuario` | `INT` | NO | FK | - | Destinatario del mensaje |
| `id_caso` | `INT` | SÍ | FK | NULL | Caso asociado causante de la notificación |
| `titulo` | `VARCHAR(120)` | NO | - | - | Asunto o título breve |
| `mensaje` | `TEXT` | NO | - | - | Cuerpo detallado del aviso |
| `canal` | `VARCHAR(20)` | NO | CHECK | 'sistema' | `correo`, `sistema`, `push` |
| `fecha_envio` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Momento de despacho |
| `leida` | `BOOLEAN` | NO | - | `FALSE` | Marca de lectura |

---

## 18. Tabla: `reporte`
Configuración y generación de métricas por Dirección/Administrador (P5).
- **Relación:** R23 hacia `usuario` (1:N).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_reporte` | `SERIAL` | NO | PK | Auto | Identificador del reporte |
| `id_usuario` | `INT` | NO | FK | - | Administrador solicitante |
| `tipo_reporte` | `VARCHAR(50)` | NO | CHECK | - | `casos_atendidos`, `estudiantes_vulnerables`, `beneficiarios_comedor`, etc. |
| `periodo_inicio` | `DATE` | NO | - | - | Inicio del rango evaluado |
| `periodo_fin` | `DATE` | NO | - | - | Fin del rango evaluado (`fin >= inicio`) |
| `formato` | `VARCHAR(10)` | NO | CHECK | - | `PDF`, `EXCEL`, `PANTALLA` |
| `fecha_generacion` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha y hora de emisión |

---

## 19. Tabla Pivote: `estudiante_tipo_condicion` (N:M 'presenta')
Relación N:M entre Estudiante y Condiciones Médicas / Discapacidad / TEA.
- **Relación:** R24 (`id_estudiante` + `id_tipo_condicion` forman la PK compuesta).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_estudiante` | `INT` | NO | PK, FK | - | Referencia al estudiante |
| `id_tipo_condicion` | `INT` | NO | PK, FK | - | Referencia a la condición |
| `grado` | `VARCHAR(20)` | NO | CHECK | - | `leve`, `moderado`, `severo` |
| `requiere_aula_accesible` | `BOOLEAN` | NO | - | `FALSE` | Ley 29973: asignación a 1er piso / ascensor |
| `pauta_pedagogica` | `TEXT` | SÍ | - | NULL | Ley 30150: adaptación metodológica para el docente |
| `certificado_conadis` | `VARCHAR(50)` | SÍ | - | NULL | Código de carnet CONADIS |
| `visible_docente` | `BOOLEAN` | NO | - | `FALSE` | Permite al docente leer solo la pauta pedagógica |
| `fecha_registro` | `TIMESTAMP` | NO | - | `CURRENT_TIMESTAMP` | Fecha de vinculación |

---

## 20. Tabla Pivote: `estudiante_deporte` (N:M 'practica')
Relación N:M entre Estudiante y Disciplinas Deportivas.
- **Relación:** R25 (`id_estudiante` + `id_deporte` forman la PK compuesta).

| Campo | Tipo | Nulo | Clave | Default | Descripción |
|---|---|---|---|---|---|
| `id_estudiante` | `INT` | NO | PK, FK | - | Referencia al estudiante |
| `id_deporte` | `INT` | NO | PK, FK | - | Referencia al deporte |
| `nivel` | `VARCHAR(20)` | NO | CHECK | - | `principiante`, `intermedio`, `avanzado`, `federado` |
| `disponible_seleccion` | `BOOLEAN` | NO | - | `TRUE` | Habilitado para selecciones de informática UNT |

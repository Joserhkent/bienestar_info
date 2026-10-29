-- ============================================================================
-- PROYECTO: Portal de Gestión de Dirección de Escuela - UNT
-- MÓDULO: Bienestar Estudiantil (BienestarTutoriaModule) - Grupo 11
-- MOTOR: PostgreSQL 15+ / 16+
-- ENTREGABLE 1: Modelo Lógico y Físico (DDL) en esquema "bienestar"
-- ============================================================================

CREATE SCHEMA IF NOT EXISTS bienestar;

SET search_path TO bienestar, public;

-- Limpieza preventiva si se requiere reejecutar (orden inverso de dependencias)
DROP TABLE IF EXISTS bienestar.estudiante_deporte CASCADE;
DROP TABLE IF EXISTS bienestar.estudiante_tipo_condicion CASCADE;
DROP TABLE IF EXISTS bienestar.reporte CASCADE;
DROP TABLE IF EXISTS bienestar.notificacion CASCADE;
DROP TABLE IF EXISTS bienestar.cita CASCADE;
DROP TABLE IF EXISTS bienestar.horario_disponible CASCADE;
DROP TABLE IF EXISTS bienestar.documento_adjunto CASCADE;
DROP TABLE IF EXISTS bienestar.solicitud_apoyo CASCADE;
DROP TABLE IF EXISTS bienestar.seguimiento CASCADE;
DROP TABLE IF EXISTS bienestar.derivacion CASCADE;
DROP TABLE IF EXISTS bienestar.historial_estado_caso CASCADE;
DROP TABLE IF EXISTS bienestar.caso_bienestar CASCADE;
DROP TABLE IF EXISTS bienestar.tipo_caso CASCADE;
DROP TABLE IF EXISTS bienestar.deporte CASCADE;
DROP TABLE IF EXISTS bienestar.tipo_condicion CASCADE;
DROP TABLE IF EXISTS bienestar.perfil_socioeconomico CASCADE;
DROP TABLE IF EXISTS bienestar.especialista CASCADE;
DROP TABLE IF EXISTS bienestar.estudiante CASCADE;
DROP TABLE IF EXISTS bienestar.usuario CASCADE;
DROP TABLE IF EXISTS bienestar.rol CASCADE;

-- ----------------------------------------------------------------------------
-- 1. TABLA: ROL
-- R01: ROL (0,N) asigna (1,1) USUARIO
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.rol (
    id_rol SERIAL PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
);

COMMENT ON TABLE bienestar.rol IS 'Roles del sistema: Estudiante, Personal de Bienestar, Especialista, Administrador, Docente.';

-- ----------------------------------------------------------------------------
-- 2. TABLA: USUARIO
-- Seguridad, login, autenticación y sesión
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.usuario (
    id_usuario SERIAL PRIMARY KEY,
    id_rol INT NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo', 'bloqueado')),
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP,
    CONSTRAINT fk_usuario_rol FOREIGN KEY (id_rol) 
        REFERENCES bienestar.rol (id_rol) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_usuario_correo ON bienestar.usuario (correo);
CREATE INDEX idx_usuario_rol ON bienestar.usuario (id_rol);

-- ----------------------------------------------------------------------------
-- 3. TABLA: ESTUDIANTE
-- R02: USUARIO (0,1) identifica a (1,1) ESTUDIANTE (1:1 estricto con id_usuario UNIQUE)
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.estudiante (
    id_estudiante SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    codigo_universitario VARCHAR(10) NOT NULL UNIQUE,
    dni VARCHAR(8) NOT NULL UNIQUE,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    sexo CHAR(1) NOT NULL CHECK (sexo IN ('M', 'F', 'O')),
    fecha_nacimiento DATE NOT NULL,
    telefono VARCHAR(15),
    direccion_actual VARCHAR(255),
    zona_procedencia VARCHAR(50) NOT NULL CHECK (zona_procedencia IN ('costa', 'sierra', 'selva', 'otra')),
    lugar_procedencia VARCHAR(100) NOT NULL,
    ciclo_actual INT NOT NULL CHECK (ciclo_actual BETWEEN 1 AND 10),
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiante_usuario FOREIGN KEY (id_usuario) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_estudiante_codigo ON bienestar.estudiante (codigo_universitario);
CREATE INDEX idx_estudiante_dni ON bienestar.estudiante (dni);

-- ----------------------------------------------------------------------------
-- 4. TABLA: ESPECIALISTA
-- R03: USUARIO (0,1) identifica a (1,1) ESPECIALISTA (1:1 estricto con id_usuario UNIQUE)
-- Psicólogos, trabajadores sociales, tutores
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.especialista (
    id_especialista SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    nro_colegiatura VARCHAR(20) NOT NULL UNIQUE,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo' CHECK (estado IN ('activo', 'inactivo', 'licencia')),
    CONSTRAINT fk_especialista_usuario FOREIGN KEY (id_usuario) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_especialista_colegiatura ON bienestar.especialista (nro_colegiatura);

-- ----------------------------------------------------------------------------
-- 5. TABLA: PERFIL_SOCIOECONOMICO
-- R04: ESTUDIANTE (0,1) tiene (1,1) PERFIL_SOCIOECONOMICO (1:1 con id_estudiante UNIQUE)
-- Criterio SISFOH, vulnerabilidad, Ley Universitaria
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.perfil_socioeconomico (
    id_perfil SERIAL PRIMARY KEY,
    id_estudiante INT NOT NULL UNIQUE,
    clasificacion_sisfoh VARCHAR(25) NOT NULL CHECK (clasificacion_sisfoh IN ('no_pobre', 'pobre', 'pobre_extremo')),
    violencia_familiar BOOLEAN NOT NULL DEFAULT FALSE,
    situacion_orfandad VARCHAR(20) NOT NULL DEFAULT 'ninguna' CHECK (situacion_orfandad IN ('ninguna', 'padre', 'madre', 'ambos')),
    es_sustento_familiar BOOLEAN NOT NULL DEFAULT FALSE,
    nro_dependientes INT NOT NULL DEFAULT 0 CHECK (nro_dependientes >= 0),
    observacion TEXT,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_perfil_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE CASCADE
);

-- ----------------------------------------------------------------------------
-- 6. TABLA: TIPO_CONDICION
-- Catálogo de condiciones médicas/discapacidad/neurodivergencia
-- Base legal: Ley 29973 (Discapacidad) y Ley 30150 (TEA)
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.tipo_condicion (
    id_tipo_condicion SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    categoria VARCHAR(50) NOT NULL CHECK (categoria IN ('fisica_movilidad', 'sensorial_visual', 'sensorial_auditiva', 'neurodivergencia_tea', 'otra')),
    base_legal VARCHAR(100) NOT NULL
);

-- ----------------------------------------------------------------------------
-- 7. TABLA: DEPORTE
-- Catálogo extracurricular / selecciones UNT
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.deporte (
    id_deporte SERIAL PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL UNIQUE
);

-- ----------------------------------------------------------------------------
-- 8. TABLA: TIPO_CASO
-- Catálogo de tipos de casos de bienestar (salud mental, académico, socioeconómico, convivencia)
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.tipo_caso (
    id_tipo_caso SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255)
);

-- ----------------------------------------------------------------------------
-- 9. TABLA: CASO_BIENESTAR
-- Proceso P1 y base de derivaciones.
-- R05: ESTUDIANTE (0,N) genera (1,1) CASO_BIENESTAR
-- R06: TIPO_CASO (0,N) clasifica (1,1) CASO_BIENESTAR
-- R07: USUARIO (0,N) registra (1,1) CASO_BIENESTAR
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.caso_bienestar (
    id_caso SERIAL PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_tipo_caso INT NOT NULL,
    id_usuario_registro INT NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'registrado' 
        CHECK (estado IN ('registrado', 'en_atencion', 'derivado', 'en_seguimiento', 'cerrado')),
    prioridad VARCHAR(15) NOT NULL DEFAULT 'media' 
        CHECK (prioridad IN ('baja', 'media', 'alta', 'urgente')),
    confidencial BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP,
    CONSTRAINT fk_caso_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_caso_tipo FOREIGN KEY (id_tipo_caso) 
        REFERENCES bienestar.tipo_caso (id_tipo_caso) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_caso_usuario_reg FOREIGN KEY (id_usuario_registro) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_caso_estudiante ON bienestar.caso_bienestar (id_estudiante);
CREATE INDEX idx_caso_estado ON bienestar.caso_bienestar (estado);
CREATE INDEX idx_caso_confidencial ON bienestar.caso_bienestar (confidencial);

-- ----------------------------------------------------------------------------
-- 10. TABLA: HISTORIAL_ESTADO_CASO
-- Trazabilidad y auditoría de cambios de estado (R08 y R09)
-- R08: CASO_BIENESTAR (1,N) registra cambios (1,1) HISTORIAL_ESTADO_CASO
-- R09: USUARIO (0,N) realiza (1,1) HISTORIAL_ESTADO_CASO
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.historial_estado_caso (
    id_historial SERIAL PRIMARY KEY,
    id_caso INT NOT NULL,
    id_usuario INT NOT NULL,
    estado_anterior VARCHAR(20),
    estado_nuevo VARCHAR(20) NOT NULL,
    fecha_cambio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    comentario TEXT,
    CONSTRAINT fk_historial_caso FOREIGN KEY (id_caso) 
        REFERENCES bienestar.caso_bienestar (id_caso) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_historial_caso ON bienestar.historial_estado_caso (id_caso);

-- ----------------------------------------------------------------------------
-- 11. TABLA: DERIVACION
-- Proceso P4 (Derivación externa/interna a DBU / Especialistas)
-- R10: USUARIO (0,N) deriva (1,1) DERIVACION
-- R11: CASO_BIENESTAR (0,N) se deriva (1,1) DERIVACION
-- R12: ESPECIALISTA (0,N) recibe (1,1) DERIVACION
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.derivacion (
    id_derivacion SERIAL PRIMARY KEY,
    id_caso INT NOT NULL,
    id_especialista INT NOT NULL,
    id_usuario_deriva INT NOT NULL,
    motivo TEXT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente' 
        CHECK (estado IN ('pendiente', 'en_atencion', 'concluida', 'rechazada')),
    fecha_derivacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_derivacion_caso FOREIGN KEY (id_caso) 
        REFERENCES bienestar.caso_bienestar (id_caso) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_derivacion_especialista FOREIGN KEY (id_especialista) 
        REFERENCES bienestar.especialista (id_especialista) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_derivacion_usuario_deriva FOREIGN KEY (id_usuario_deriva) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_derivacion_caso ON bienestar.derivacion (id_caso);
CREATE INDEX idx_derivacion_especialista ON bienestar.derivacion (id_especialista);

-- ----------------------------------------------------------------------------
-- 12. TABLA: SEGUIMIENTO
-- Ciclos y sesiones de atención del Especialista (R13)
-- R13: DERIVACION (0,N) tiene (1,1) SEGUIMIENTO
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.seguimiento (
    id_seguimiento SERIAL PRIMARY KEY,
    id_derivacion INT NOT NULL,
    fecha_sesion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    nota_avance TEXT NOT NULL,
    acuerdos TEXT,
    proxima_fecha TIMESTAMP,
    CONSTRAINT fk_seguimiento_derivacion FOREIGN KEY (id_derivacion) 
        REFERENCES bienestar.derivacion (id_derivacion) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX idx_seguimiento_derivacion ON bienestar.seguimiento (id_derivacion);

-- ----------------------------------------------------------------------------
-- 13. TABLA: SOLICITUD_APOYO
-- Proceso P2: Comedor, becas, apoyo socioeconómico
-- R14: ESTUDIANTE (0,N) envía (1,1) SOLICITUD_APOYO
-- R15: USUARIO (0,N) revisa (0,1) SOLICITUD_APOYO (NULLABLE)
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.solicitud_apoyo (
    id_solicitud SERIAL PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_usuario_revisa INT, -- Nullable hasta que un Personal de Bienestar la tome
    tipo_apoyo VARCHAR(50) NOT NULL CHECK (tipo_apoyo IN ('comedor_universitario', 'beca_alimentaria', 'subsidio_transporte', 'exoneracion_pago', 'otro')),
    motivo TEXT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'enviada' 
        CHECK (estado IN ('enviada', 'en_revision', 'aprobada', 'rechazada')),
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion TIMESTAMP,
    observacion TEXT,
    CONSTRAINT fk_solicitud_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitud_usuario_revisa FOREIGN KEY (id_usuario_revisa) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX idx_solicitud_estudiante ON bienestar.solicitud_apoyo (id_estudiante);
CREATE INDEX idx_solicitud_estado ON bienestar.solicitud_apoyo (estado);

-- ----------------------------------------------------------------------------
-- 14. TABLA: DOCUMENTO_ADJUNTO
-- Sustento de solicitudes (SISFOH, constancias, recibos de luz/agua)
-- R16: SOLICITUD_APOYO (1,N) adjunta (1,1) DOCUMENTO_ADJUNTO
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.documento_adjunto (
    id_documento SERIAL PRIMARY KEY,
    id_solicitud INT NOT NULL,
    tipo_documento VARCHAR(80) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tamano_kb INT NOT NULL CHECK (tamano_kb > 0),
    fecha_subida TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_documento_solicitud FOREIGN KEY (id_solicitud) 
        REFERENCES bienestar.solicitud_apoyo (id_solicitud) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE INDEX idx_documento_solicitud ON bienestar.documento_adjunto (id_solicitud);

-- ----------------------------------------------------------------------------
-- 15. TABLA: HORARIO_DISPONIBLE
-- Horarios habilitados por el Especialista
-- R17: ESPECIALISTA (0,N) ofrece (1,1) HORARIO_DISPONIBLE
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.horario_disponible (
    id_horario SERIAL PRIMARY KEY,
    id_especialista INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'disponible' CHECK (estado IN ('disponible', 'reservado', 'bloqueado')),
    CONSTRAINT fk_horario_especialista FOREIGN KEY (id_especialista) 
        REFERENCES bienestar.especialista (id_especialista) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_horario_coherencia CHECK (hora_fin > hora_inicio)
);

CREATE INDEX idx_horario_especialista_fecha ON bienestar.horario_disponible (id_especialista, fecha);

-- ----------------------------------------------------------------------------
-- 16. TABLA: CITA
-- Proceso P3: Agendamiento de citas
-- R18: HORARIO_DISPONIBLE (0,1) se reserva en (1,1) CITA
-- R19: ESTUDIANTE (0,N) agenda (1,1) CITA
-- R20: CASO_BIENESTAR (0,N) origina (0,1) CITA (NULLABLE)
-- Nota: La restricción de unicidad es parcial (uq_cita_horario_activo) para permitir
-- reutilizar un horario si la cita anterior fue cancelada.
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.cita (
    id_cita SERIAL PRIMARY KEY,
    id_horario INT NOT NULL,
    id_estudiante INT NOT NULL,
    id_caso INT, -- Nullable: una cita puede ser espontánea o ligada a un caso
    tipo_cita VARCHAR(30) NOT NULL CHECK (tipo_cita IN ('psicologia', 'tutoria_academica', 'apoyo_social', 'adaptada_tea')),
    estado VARCHAR(20) NOT NULL DEFAULT 'solicitada' 
        CHECK (estado IN ('solicitada', 'confirmada', 'realizada', 'cancelada')),
    motivo VARCHAR(255) NOT NULL,
    fecha_solicitud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_confirmacion TIMESTAMP,
    CONSTRAINT fk_cita_horario FOREIGN KEY (id_horario) 
        REFERENCES bienestar.horario_disponible (id_horario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cita_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_cita_caso FOREIGN KEY (id_caso) 
        REFERENCES bienestar.caso_bienestar (id_caso) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Índice único parcial: Un horario no puede tener dos citas activas,
-- pero si una cita se cancela, el horario queda libre para agendarse nuevamente.
CREATE UNIQUE INDEX uq_cita_horario_activo 
    ON bienestar.cita (id_horario) 
    WHERE estado != 'cancelada';

CREATE INDEX idx_cita_estudiante ON bienestar.cita (id_estudiante);
CREATE INDEX idx_cita_horario ON bienestar.cita (id_horario);
CREATE INDEX idx_cita_estado ON bienestar.cita (estado);

-- ----------------------------------------------------------------------------
-- 17. TABLA: NOTIFICACION
-- Proceso P5: Mensajería del sistema y alertas
-- R21: USUARIO (0,N) recibe (1,1) NOTIFICACION
-- R22: CASO_BIENESTAR (0,N) dispara (0,1) NOTIFICACION (NULLABLE)
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.notificacion (
    id_notificacion SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_caso INT, -- Nullable: puede asociarse a un caso o a una solicitud/cita
    titulo VARCHAR(120) NOT NULL,
    mensaje TEXT NOT NULL,
    canal VARCHAR(20) NOT NULL DEFAULT 'sistema' CHECK (canal IN ('correo', 'sistema', 'push')),
    fecha_envio TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leida BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_notificacion_usuario FOREIGN KEY (id_usuario) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_notificacion_caso FOREIGN KEY (id_caso) 
        REFERENCES bienestar.caso_bienestar (id_caso) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE INDEX idx_notificacion_usuario_leida ON bienestar.notificacion (id_usuario, leida);

-- ----------------------------------------------------------------------------
-- 18. TABLA: REPORTE
-- Proceso P5: Parametrización y generación por Administrador/Dirección
-- R23: USUARIO (0,N) genera (1,1) REPORTE
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.reporte (
    id_reporte SERIAL PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo_reporte VARCHAR(50) NOT NULL 
        CHECK (tipo_reporte IN ('casos_atendidos', 'estudiantes_vulnerables', 'beneficiarios_comedor', 'citas_psicologia', 'consolidado_general')),
    periodo_inicio DATE NOT NULL,
    periodo_fin DATE NOT NULL,
    formato VARCHAR(10) NOT NULL CHECK (formato IN ('PDF', 'EXCEL', 'PANTALLA')),
    fecha_generacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_usuario FOREIGN KEY (id_usuario) 
        REFERENCES bienestar.usuario (id_usuario) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_reporte_fechas CHECK (periodo_fin >= periodo_inicio)
);

CREATE INDEX idx_reporte_usuario ON bienestar.reporte (id_usuario);

-- ----------------------------------------------------------------------------
-- 19. TABLA PIVOTE: ESTUDIANTE_TIPO_CONDICION (N:M 'presenta')
-- R24: ESTUDIANTE (0,N) presenta (0,N) TIPO_CONDICION
-- Atributos propios: grado, requiere_aula_accesible, pauta_pedagogica,
-- certificado_conadis, visible_docente, fecha_registro
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.estudiante_tipo_condicion (
    id_estudiante INT NOT NULL,
    id_tipo_condicion INT NOT NULL,
    grado VARCHAR(20) NOT NULL CHECK (grado IN ('leve', 'moderado', 'severo')),
    requiere_aula_accesible BOOLEAN NOT NULL DEFAULT FALSE,
    pauta_pedagogica TEXT, -- Visible al docente si visible_docente = true (Ley 30150 TEA)
    certificado_conadis VARCHAR(50),
    visible_docente BOOLEAN NOT NULL DEFAULT FALSE,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_estudiante, id_tipo_condicion),
    CONSTRAINT fk_pivot_condicion_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pivot_condicion_tipo FOREIGN KEY (id_tipo_condicion) 
        REFERENCES bienestar.tipo_condicion (id_tipo_condicion) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_pivot_condicion_accesible ON bienestar.estudiante_tipo_condicion (requiere_aula_accesible);
CREATE INDEX idx_pivot_condicion_docente ON bienestar.estudiante_tipo_condicion (visible_docente);

-- ----------------------------------------------------------------------------
-- 20. TABLA PIVOTE: ESTUDIANTE_DEPORTE (N:M 'practica')
-- R25: ESTUDIANTE (0,N) practica (0,N) DEPORTE
-- Atributos propios: nivel, disponible_seleccion
-- ----------------------------------------------------------------------------
CREATE TABLE bienestar.estudiante_deporte (
    id_estudiante INT NOT NULL,
    id_deporte INT NOT NULL,
    nivel VARCHAR(20) NOT NULL CHECK (nivel IN ('principiante', 'intermedio', 'avanzado', 'federado')),
    disponible_seleccion BOOLEAN NOT NULL DEFAULT TRUE,
    PRIMARY KEY (id_estudiante, id_deporte),
    CONSTRAINT fk_pivot_deporte_estudiante FOREIGN KEY (id_estudiante) 
        REFERENCES bienestar.estudiante (id_estudiante) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_pivot_deporte_deporte FOREIGN KEY (id_deporte) 
        REFERENCES bienestar.deporte (id_deporte) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE INDEX idx_pivot_deporte_seleccion ON bienestar.estudiante_deporte (disponible_seleccion);

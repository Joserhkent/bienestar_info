<?php

namespace App\Modules\Bienestar\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BienestarDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ejecutar catálogos base
        $this->call([
            RolSeeder::class,
            TipoCondicionSeeder::class,
            DeporteSeeder::class,
            TipoCasoSeeder::class,
        ]);

        $passwordHash = Hash::make('password123');

        // 2. Usuarios
        $usuarios = [
            // Admin
            ['id_usuario' => 1, 'id_rol' => 5, 'correo' => 'admin@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Personal de Bienestar
            ['id_usuario' => 2, 'id_rol' => 2, 'correo' => 'bienestar@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Especialista Psicología
            ['id_usuario' => 3, 'id_rol' => 3, 'correo' => 'psicologia@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Especialista Tutoría
            ['id_usuario' => 4, 'id_rol' => 3, 'correo' => 'tutor@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Docente
            ['id_usuario' => 5, 'id_rol' => 4, 'correo' => 'docente@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Estudiante 1 (Comedor / SISFOH)
            ['id_usuario' => 6, 'id_rol' => 1, 'correo' => 'estudiante1@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Estudiante 2 (TEA / Pauta pedagógica)
            ['id_usuario' => 7, 'id_rol' => 1, 'correo' => 'estudiante2@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Estudiante 3 (Discapacidad / Aula accesible)
            ['id_usuario' => 8, 'id_rol' => 1, 'correo' => 'estudiante3@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
            // Estudiante 4 (Deportista selección)
            ['id_usuario' => 9, 'id_rol' => 1, 'correo' => 'estudiante4@unitru.edu.pe', 'contrasena_hash' => $passwordHash, 'estado' => 'activo'],
        ];

        foreach ($usuarios as $u) {
            DB::table('bienestar.usuario')->updateOrInsert(['id_usuario' => $u['id_usuario']], $u);
        }

        // 3. Especialistas
        DB::table('bienestar.especialista')->updateOrInsert(['id_especialista' => 1], [
            'id_especialista' => 1,
            'id_usuario' => 3,
            'nombres' => 'Silvia Marina',
            'apellidos' => 'Vargas Rojas',
            'especialidad' => 'Psicología Clínica y Educativa',
            'nro_colegiatura' => 'C.Ps.P. 18452',
            'estado' => 'activo',
        ]);

        DB::table('bienestar.especialista')->updateOrInsert(['id_especialista' => 2], [
            'id_especialista' => 2,
            'id_usuario' => 4,
            'nombres' => 'Jorge Luis',
            'apellidos' => 'Alva Mantilla',
            'especialidad' => 'Tutoría Universitaria e Inclusión',
            'nro_colegiatura' => 'COL-DOC 9032',
            'estado' => 'activo',
        ]);

        // 4. Estudiantes
        $estudiantes = [
            [
                'id_estudiante' => 1,
                'id_usuario' => 6,
                'codigo_universitario' => '1021400121',
                'dni' => '72345671',
                'nombres' => 'Carlos Alberto',
                'apellidos' => 'Mendoza Castillo',
                'sexo' => 'M',
                'fecha_nacimiento' => '2003-04-15',
                'telefono' => '944123456',
                'direccion_actual' => 'Urb. Monserrate Mz D Lote 4, Trujillo',
                'zona_procedencia' => 'sierra',
                'lugar_procedencia' => 'Huamachuco, Sánchez Carrión',
                'ciclo_actual' => 5,
            ],
            [
                'id_estudiante' => 2,
                'id_usuario' => 7,
                'codigo_universitario' => '1021400222',
                'dni' => '73456782',
                'nombres' => 'Andrea Beatriz',
                'apellidos' => 'Paredes Benites',
                'sexo' => 'F',
                'fecha_nacimiento' => '2004-09-20',
                'telefono' => '948234567',
                'direccion_actual' => 'Av. Larco 1240, Víctor Larco Herrera',
                'zona_procedencia' => 'costa',
                'lugar_procedencia' => 'Trujillo',
                'ciclo_actual' => 4,
            ],
            [
                'id_estudiante' => 3,
                'id_usuario' => 8,
                'codigo_universitario' => '1021400323',
                'dni' => '74567893',
                'nombres' => 'Kevin Daniel',
                'apellidos' => 'Quispe Saldaña',
                'sexo' => 'M',
                'fecha_nacimiento' => '2002-11-08',
                'telefono' => '949345678',
                'direccion_actual' => 'Calle Los Jazmines 340, California',
                'zona_procedencia' => 'costa',
                'lugar_procedencia' => 'Chepén',
                'ciclo_actual' => 6,
            ],
            [
                'id_estudiante' => 4,
                'id_usuario' => 9,
                'codigo_universitario' => '1021400424',
                'dni' => '75678904',
                'nombres' => 'Lucía Fernanda',
                'apellidos' => 'Rojas Chávez',
                'sexo' => 'F',
                'fecha_nacimiento' => '2003-01-25',
                'telefono' => '941456789',
                'direccion_actual' => 'Av. América Sur 2890, Trujillo',
                'zona_procedencia' => 'costa',
                'lugar_procedencia' => 'Trujillo',
                'ciclo_actual' => 5,
            ],
        ];

        foreach ($estudiantes as $est) {
            DB::table('bienestar.estudiante')->updateOrInsert(['id_estudiante' => $est['id_estudiante']], $est);
        }

        // 5. Perfiles socioeconómicos
        DB::table('bienestar.perfil_socioeconomico')->updateOrInsert(['id_estudiante' => 1], [
            'id_perfil' => 1,
            'id_estudiante' => 1,
            'clasificacion_sisfoh' => 'pobre_extremo',
            'violencia_familiar' => false,
            'situacion_orfandad' => 'padre',
            'es_sustento_familiar' => true,
            'nro_dependientes' => 2,
            'observacion' => Crypt::encryptString('Postulante prioritario para Comedor Universitario. Vive en pensión con hermanos menores.'),
        ]);

        DB::table('bienestar.perfil_socioeconomico')->updateOrInsert(['id_estudiante' => 2], [
            'id_perfil' => 2,
            'id_estudiante' => 2,
            'clasificacion_sisfoh' => 'no_pobre',
            'violencia_familiar' => false,
            'situacion_orfandad' => 'ninguna',
            'es_sustento_familiar' => false,
            'nro_dependientes' => 0,
            'observacion' => Crypt::encryptString('Soporte familiar estable. Seguimiento enfocado en adaptación académica TEA.'),
        ]);

        DB::table('bienestar.perfil_socioeconomico')->updateOrInsert(['id_estudiante' => 3], [
            'id_perfil' => 3,
            'id_estudiante' => 3,
            'clasificacion_sisfoh' => 'pobre',
            'violencia_familiar' => false,
            'situacion_orfandad' => 'ninguna',
            'es_sustento_familiar' => false,
            'nro_dependientes' => 0,
            'observacion' => Crypt::encryptString('Requiere apoyo de movilidad asistida dentro de la Ciudad Universitaria.'),
        ]);

        DB::table('bienestar.perfil_socioeconomico')->updateOrInsert(['id_estudiante' => 4], [
            'id_perfil' => 4,
            'id_estudiante' => 4,
            'clasificacion_sisfoh' => 'no_pobre',
            'violencia_familiar' => false,
            'situacion_orfandad' => 'ninguna',
            'es_sustento_familiar' => false,
            'nro_dependientes' => 0,
            'observacion' => Crypt::encryptString('Deportista calificada sin observaciones de vulnerabilidad económica.'),
        ]);

        // 6. Condiciones médicas / TEA (presenta)
        // Estudiante 2: TEA con pauta pedagógica visible al docente
        DB::table('bienestar.estudiante_tipo_condicion')->updateOrInsert(
            ['id_estudiante' => 2, 'id_tipo_condicion' => 1],
            [
                'id_estudiante' => 2,
                'id_tipo_condicion' => 1,
                'grado' => 'moderado',
                'requiere_aula_accesible' => false,
                'pauta_pedagogica' => Crypt::encryptString('Dar instrucciones explícitas y estructuradas por escrito en plataforma virtual. Evitar cambios imprevistos en fechas de entregables. En trabajos grupales, permitir opción de rol individualizado en programación.'),
                'certificado_conadis' => 'CONADIS-TEA-2023-0941',
                'visible_docente' => true,
            ]
        );

        // Estudiante 3: Discapacidad motriz (requiere aula accesible)
        DB::table('bienestar.estudiante_tipo_condicion')->updateOrInsert(
            ['id_estudiante' => 3, 'id_tipo_condicion' => 2],
            [
                'id_estudiante' => 3,
                'id_tipo_condicion' => 2,
                'grado' => 'severo',
                'requiere_aula_accesible' => true,
                'pauta_pedagogica' => Crypt::encryptString('Garantizar asignación de laboratorios y aulas en primer piso o edificios con rampa y ascensor operativo.'),
                'certificado_conadis' => 'CONADIS-MOT-2022-4412',
                'visible_docente' => true,
            ]
        );

        // 7. Deportes (practica)
        DB::table('bienestar.estudiante_deporte')->updateOrInsert(
            ['id_estudiante' => 4, 'id_deporte' => 3],
            [
                'id_estudiante' => 4,
                'id_deporte' => 3,
                'nivel' => 'avanzado',
                'disponible_seleccion' => true,
            ]
        );
        DB::table('bienestar.estudiante_deporte')->updateOrInsert(
            ['id_estudiante' => 1, 'id_deporte' => 1],
            [
                'id_estudiante' => 1,
                'id_deporte' => 1,
                'nivel' => 'intermedio',
                'disponible_seleccion' => true,
            ]
        );

        // 8. Horarios disponibles ofrecidos por Especialistas
        DB::table('bienestar.horario_disponible')->updateOrInsert(['id_horario' => 1], [
            'id_horario' => 1,
            'id_especialista' => 1,
            'fecha' => '2026-11-05',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:00:00',
            'estado' => 'reservado',
        ]);
        DB::table('bienestar.horario_disponible')->updateOrInsert(['id_horario' => 2], [
            'id_horario' => 2,
            'id_especialista' => 1,
            'fecha' => '2026-11-05',
            'hora_inicio' => '10:30:00',
            'hora_fin' => '11:30:00',
            'estado' => 'disponible',
        ]);
        DB::table('bienestar.horario_disponible')->updateOrInsert(['id_horario' => 3], [
            'id_horario' => 3,
            'id_especialista' => 2,
            'fecha' => '2026-11-06',
            'hora_inicio' => '15:00:00',
            'hora_fin' => '16:00:00',
            'estado' => 'disponible',
        ]);

        // 9. Casos de Bienestar (P1)
        DB::table('bienestar.caso_bienestar')->updateOrInsert(['id_caso' => 1], [
            'id_caso' => 1,
            'id_estudiante' => 1,
            'id_tipo_caso' => 3,
            'id_usuario_registro' => 2,
            'descripcion' => 'Estudiante reporta extrema vulnerabilidad económica tras fallecimiento de su progenitor. Requiere comedor urgente.',
            'estado' => 'en_seguimiento',
            'prioridad' => 'urgente',
            'confidencial' => true,
            'fecha_registro' => '2026-10-20 10:00:00',
        ]);

        DB::table('bienestar.historial_estado_caso')->updateOrInsert(['id_historial' => 1], [
            'id_historial' => 1,
            'id_caso' => 1,
            'id_usuario' => 2,
            'estado_anterior' => null,
            'estado_nuevo' => 'registrado',
            'fecha_cambio' => '2026-10-20 10:00:00',
            'comentario' => 'Apertura inicial del expediente socioeconómico.',
        ]);

        DB::table('bienestar.historial_estado_caso')->updateOrInsert(['id_historial' => 2], [
            'id_historial' => 2,
            'id_caso' => 1,
            'id_usuario' => 2,
            'estado_anterior' => 'registrado',
            'estado_nuevo' => 'en_seguimiento',
            'fecha_cambio' => '2026-10-22 11:30:00',
            'comentario' => 'Derivado a evaluación de asistente social y seguimiento tutor.',
        ]);

        // 10. Derivación y seguimiento (P4)
        DB::table('bienestar.derivacion')->updateOrInsert(['id_derivacion' => 1], [
            'id_derivacion' => 1,
            'id_caso' => 1,
            'id_especialista' => 2,
            'id_usuario_deriva' => 2,
            'motivo' => 'Monitoreo de rendimiento académico ante carga laboral nocturna del estudiante.',
            'estado' => 'en_atencion',
            'fecha_derivacion' => '2026-10-22 11:30:00',
        ]);

        DB::table('bienestar.seguimiento')->updateOrInsert(['id_seguimiento' => 1], [
            'id_seguimiento' => 1,
            'id_derivacion' => 1,
            'fecha_sesion' => '2026-10-24 16:00:00',
            'nota_avance' => Crypt::encryptString('Primera sesión sostenida. Se elaboró horario de estudio compatible con su jornada de trabajo de fin de semana.'),
            'acuerdos' => Crypt::encryptString('Estudiante se compromete a asistir a asesorías de Algoritmos los jueves.'),
            'proxima_fecha' => '2026-11-07 16:00:00',
        ]);

        // 11. Solicitud de Apoyo (P2)
        DB::table('bienestar.solicitud_apoyo')->updateOrInsert(['id_solicitud' => 1], [
            'id_solicitud' => 1,
            'id_estudiante' => 1,
            'id_usuario_revisa' => 2,
            'tipo_apoyo' => 'comedor_universitario',
            'motivo' => 'Carencia de recursos económicos para alimentación diaria durante el horario corrido de clases en la facultad.',
            'estado' => 'aprobada',
            'fecha_envio' => '2026-10-21 08:30:00',
            'fecha_resolucion' => '2026-10-23 12:00:00',
            'observacion' => 'Aprobado según Ficha SISFOH Pobreza Extrema y visita domiciliaria.',
        ]);

        DB::table('bienestar.documento_adjunto')->updateOrInsert(['id_documento' => 1], [
            'id_documento' => 1,
            'id_solicitud' => 1,
            'tipo_documento' => 'Constancia SISFOH',
            'nombre_archivo' => 'sisfoh_clasificacion_72345671.pdf',
            'ruta_archivo' => 'documentos/solicitudes/1/sisfoh_clasificacion_72345671.pdf',
            'tamano_kb' => 380,
        ]);

        // 12. Cita (P3)
        DB::table('bienestar.cita')->updateOrInsert(['id_cita' => 1], [
            'id_cita' => 1,
            'id_horario' => 1,
            'id_estudiante' => 1,
            'id_caso' => 1,
            'tipo_cita' => 'tutoria_academica',
            'estado' => 'confirmada',
            'motivo' => Crypt::encryptString('Revisión de avance en materias críticas y bienestar emocional.'),
            'fecha_solicitud' => '2026-10-25 09:00:00',
            'fecha_confirmacion' => '2026-10-25 10:15:00',
        ]);

        // 13. Notificaciones (P5: texto genérico institucional)
        DB::table('bienestar.notificacion')->updateOrInsert(['id_notificacion' => 1], [
            'id_notificacion' => 1,
            'id_usuario' => 6, // Estudiante 1
            'id_caso' => 1,
            'titulo' => 'Actualización en tu solicitud',
            'mensaje' => 'Tienes una actualización en tu solicitud: ha sido aprobada.',
            'canal' => 'sistema',
            'fecha_envio' => '2026-10-23 12:05:00',
            'leida' => true,
        ]);

        // 14. Sincronizar secuencias de PostgreSQL para evitar colisiones de IDs en nuevas inserciones
        $tablasConSerial = [
            'rol' => 'id_rol',
            'usuario' => 'id_usuario',
            'estudiante' => 'id_estudiante',
            'especialista' => 'id_especialista',
            'perfil_socioeconomico' => 'id_perfil',
            'tipo_condicion' => 'id_tipo_condicion',
            'deporte' => 'id_deporte',
            'tipo_caso' => 'id_tipo_caso',
            'caso_bienestar' => 'id_caso',
            'historial_estado_caso' => 'id_historial',
            'derivacion' => 'id_derivacion',
            'seguimiento' => 'id_seguimiento',
            'solicitud_apoyo' => 'id_solicitud',
            'documento_adjunto' => 'id_documento',
            'horario_disponible' => 'id_horario',
            'cita' => 'id_cita',
            'notificacion' => 'id_notificacion',
            'reporte' => 'id_reporte',
        ];

        foreach ($tablasConSerial as $tabla => $pk) {
            DB::statement("SELECT setval(pg_get_serial_sequence('bienestar.{$tabla}', '{$pk}'), COALESCE((SELECT MAX({$pk}) FROM bienestar.{$tabla}), 1));");
        }
    }
}

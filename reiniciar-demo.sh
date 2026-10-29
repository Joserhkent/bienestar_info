#!/bin/bash
set -e

echo "======================================================================"
echo "PORTAL UNT — REINICIO DE BASE DE DATOS Y ENTORNO DE DEMOSTRACIÓN"
echo "======================================================================"

echo -e "\n[1/3] Ejecutando migrate:fresh con seeders completos en PostgreSQL..."
if command -v docker &> /dev/null && docker compose ps &> /dev/null; then
    docker compose exec -T app php artisan migrate:fresh --seed
else
    sudo docker compose exec -T app php artisan migrate:fresh --seed
fi

echo -e "\n[2/3] Verificando enlace simbólico de almacenamiento privado..."
if command -v docker &> /dev/null && docker compose ps &> /dev/null; then
    docker compose exec -T app php artisan storage:link || true
else
    sudo docker compose exec -T app php artisan storage:link || true
fi

echo -e "\n[3/3] Base de datos restablecida con éxito."
echo "======================================================================"
echo "CUENTAS DE PRUEBA (Contraseña para todas: password123)"
echo "======================================================================"
echo "1. Administrador:          admin@unitru.edu.pe"
echo "2. Personal de Bienestar:  bienestar@unitru.edu.pe"
echo "3. Especialista Psicología: psicologia@unitru.edu.pe"
echo "4. Especialista Tutoría:   tutor@unitru.edu.pe"
echo "5. Docente:                docente@unitru.edu.pe"
echo "6. Estudiante 1 (Comedor): estudiante1@unitru.edu.pe"
echo "7. Estudiante 2 (TEA):     estudiante2@unitru.edu.pe"
echo "8. Estudiante 3:           estudiante3@unitru.edu.pe"
echo "======================================================================"

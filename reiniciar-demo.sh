#!/bin/bash
set -e

echo "======================================================================"
echo "PORTAL UNT — REINICIO DE BASE DE DATOS Y ENTORNO DE DEMOSTRACIÓN"
echo "======================================================================"

# Determinar comando de Docker Compose (con o sin sudo según permisos de socket)
if docker compose ps &> /dev/null; then
    COMPOSE="docker compose"
elif sudo docker compose ps &> /dev/null; then
    COMPOSE="sudo docker compose"
else
    echo "ERROR: Docker Compose no está disponible o no se puede conectar al daemon de Docker."
    exit 1
fi

# 1. Verificación de seguridad local (.env)
if [ -f .env ]; then
    ENV_VAL=$(grep -E '^APP_ENV=' .env | head -n 1 | cut -d '=' -f2 | tr -d ' "'\r\n)
    if [ "$ENV_VAL" = "production" ]; then
        echo "ERROR CRÍTICO: No se puede reiniciar la base de datos en un entorno de producción (APP_ENV=production)."
        echo "Operación abortada por seguridad para prevenir pérdida de datos."
        exit 1
    fi
fi

# 2. Verificación de seguridad en el contenedor Laravel
APP_ENV_RUNTIME=$($COMPOSE exec -T app php artisan env < /dev/null 2>&1 || true)
if echo "$APP_ENV_RUNTIME" | grep -qi "production"; then
    echo "ERROR CRÍTICO: El runtime de Laravel reporta entorno 'production'."
    echo "Operación abortada por seguridad para prevenir pérdida de datos."
    exit 1
fi

echo -e "\n[1/3] Ejecutando migrate:fresh con seeders completos en PostgreSQL..."
$COMPOSE exec -T app php artisan migrate:fresh --seed < /dev/null

echo -e "\n[2/3] Verificando enlace simbólico y permisos de almacenamiento (www-data:www-data, 775)..."
$COMPOSE exec -T app php artisan storage:link < /dev/null || true
$COMPOSE exec -T app chown -R www-data:www-data storage bootstrap/cache < /dev/null || true
$COMPOSE exec -T app chmod -R 775 storage bootstrap/cache < /dev/null || true

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

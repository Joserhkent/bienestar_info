-- Inicialización del motor PostgreSQL para el Portal UNT
-- Creamos el esquema 'bienestar' en la base de datos principal
CREATE SCHEMA IF NOT EXISTS bienestar;

-- Creamos la base de datos de pruebas separada para que los tests no toquen datos de desarrollo
SELECT 'CREATE DATABASE portal_unt_test OWNER bienestar_user'
WHERE NOT EXISTS (SELECT FROM pg_database WHERE datname = 'portal_unt_test')\gexec

\connect portal_unt_test
CREATE SCHEMA IF NOT EXISTS bienestar;


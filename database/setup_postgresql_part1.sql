-- PostgreSQL setup script for Joyce Santos website - PARTE 1
-- Execute PRIMEIRO no banco 'postgres' padrão

-- Create database (Windows compatible)
CREATE DATABASE joyce_santos_website 
WITH 
    ENCODING = 'UTF8'
    TEMPLATE = template0;

-- IMPORTANTE: Após executar este script, conecte-se ao banco 'joyce_santos_website' 
-- e execute o script 'setup_postgresql_part2.sql'

#!/bin/bash

# Aguarda o banco estar disponível
until pg_isready -h $PGHOST -p $PGPORT -U $PGUSER; do
  echo "Aguardando PostgreSQL..."
  sleep 2
done

echo "PostgreSQL está pronto!"

# Executa o setup do banco de dados
php setup_database_railway.php

echo "Banco de dados configurado com sucesso!"

#!/usr/bin/env bash

mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" <<-EOSQL
    CREATE DATABASE IF NOT EXISTS $MYSQL_DATABASE;
    GRANT ALL PRIVILEGES ON \`$MYSQL_DATABASE%\`.* TO '$MYSQL_USER'@'%';
EOSQL
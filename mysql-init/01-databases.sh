#!/bin/sh
set -eu
mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS vetoros CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS vetorpet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS abrasilsistemas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'vetoros_user'@'%' IDENTIFIED BY '${VETOROS_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'vetorpet_user'@'%' IDENTIFIED BY '${VETORPET_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'abrasilsistema_user'@'%' IDENTIFIED BY '${ABRASILSISTEMA_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON vetoros.* TO 'vetoros_user'@'%';
GRANT ALL PRIVILEGES ON vetorpet.* TO 'vetorpet_user'@'%';
GRANT ALL PRIVILEGES ON abrasilsistemas.* TO 'abrasilsistema_user'@'%';
FLUSH PRIVILEGES;
SQL

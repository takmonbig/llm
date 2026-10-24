#!/bin/bash
# Preview runner: installs PHP/MariaDB if missing, starts DB, serves the site on port 3000.
set -e
SITE=/app/site
if ! command -v php >/dev/null || ! command -v mariadbd >/dev/null; then
  apt-get update -qq && DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php-cli php-mysql php-mbstring php-curl php-gd mariadb-server >/dev/null
fi
mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
if ! mysqladmin ping --silent 2>/dev/null; then
  (mariadbd --user=mysql >/var/log/mariadb-preview.log 2>&1 &)
  for i in $(seq 1 30); do mysqladmin ping --silent 2>/dev/null && break; sleep 1; done
fi
if ! mysql -e "USE longlivemen" 2>/dev/null; then
  mysql -e "CREATE DATABASE longlivemen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'llm'@'localhost' IDENTIFIED BY 'preview-only'; GRANT ALL ON longlivemen.* TO 'llm'@'localhost'; FLUSH PRIVILEGES;"
  mysql longlivemen < $SITE/database/schema.sql
  mysql longlivemen < $SITE/database/seed.sql
fi
mkdir -p $SITE/storage/logs $SITE/uploads
exec php -S 0.0.0.0:3000 -t $SITE $SITE/router.php

#!/bin/bash
set -e

composer install
#bin/console doctrine:database:create --if-not-exists -n
#bin/console doctrine:migration:migrate -n
#bin/console doctrine:fixtures:load -n

exec "$@"

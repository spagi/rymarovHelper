#!/bin/sh
set -e

# Pokud neexistuje composer.json, neděláme nic.
# Instalaci provedeme ručně.
if [ -f "composer.json" ]; then
    echo ">>> composer.json found, running composer install..."
    composer install --no-interaction --no-progress --optimize-autoloader
else
    echo ">>> composer.json not found. Please install your project."
fi

# Předání řízení hlavnímu CMD příkazu (apache2-foreground)
exec "$@"
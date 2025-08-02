#!/bin/bash

composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan serve --host=0.0.0.0 --port=${PORT}

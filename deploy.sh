#!/usr/bin/env bash
# Canlı sunucuda deploy sonrası çalıştırın. Cache'leri temizleyerek güncellemelerin hemen görünmesini sağlar.
set -e
cd "$(dirname "$0")"
php artisan optimize:clear
php artisan config:cache
echo "Deploy cache temizlendi, config cache alındı."

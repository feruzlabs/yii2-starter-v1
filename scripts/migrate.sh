#!/bin/bash

echo "📦 Running database migrations..."
docker-compose exec php-fpm php yii migrate --interactive=0

echo ""
echo "✅ Migrations completed."

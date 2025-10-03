#!/bin/bash

# Initialize Yii2 Advanced Docker project

echo "🚀 Initializing Yii2 Advanced Docker project..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created. Please edit it with your configuration."
else
    echo "⚠️  .env file already exists. Skipping..."
fi

# Build Docker images
echo "🐳 Building Docker images..."
docker-compose build

# Start services
echo "🚀 Starting services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 15

# Run migrations
echo "📦 Running database migrations..."
docker-compose exec -T php-fpm php yii migrate --interactive=0

# Install composer dependencies (if needed)
if [ ! -d "vendor" ]; then
    echo "📦 Installing Composer dependencies..."
    docker-compose exec -T php-fpm composer install
fi

echo ""
echo "✅ Initialization complete!"
echo ""
echo "📊 Service URLs:"
echo "   Frontend:  http://localhost"
echo "   API:       http://localhost/api"
echo "   RabbitMQ:  http://localhost:15672 (guest/guest)"
echo ""
echo "🔍 Useful commands:"
echo "   make logs       - View logs"
echo "   make shell      - Access PHP container"
echo "   make status     - Check service status"
echo ""

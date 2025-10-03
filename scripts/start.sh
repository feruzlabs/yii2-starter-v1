#!/bin/bash

echo "🚀 Starting all services..."
docker-compose up -d

echo "⏳ Waiting for services to be ready..."
sleep 10

echo ""
echo "✅ Services started successfully!"
echo ""
docker-compose ps

# 🚀 Application Ports

## Main Applications

| Application | Port | URL | Description |
|------------|------|-----|-------------|
| **Frontend** | 8080 | http://localhost:8080 | Public website |
| **Backend** | 8081 | http://localhost:8081 | Admin panel |
| **API** | 8082 | http://localhost:8082 | REST API |

## Services

| Service | Port | URL | Credentials |
|---------|------|-----|-------------|
| **PostgreSQL** | 5432 | localhost:5432 | yii2 / secret |
| **Redis** | 6379 | localhost:6379 | - |
| **RabbitMQ** | 5672 | localhost:5672 | guest / guest |
| **RabbitMQ Management** | 15672 | http://localhost:15672 | guest / guest |
| **PHP-FPM Status** | 9001 | http://localhost:9001/fpm-status | - |

## Quick Test Commands

```bash
# Test Frontend
curl -I http://localhost:8080

# Test Backend  
curl -I http://localhost:8081

# Test API Health
curl http://localhost:8082/health

# Test RabbitMQ Management
open http://localhost:15672
```

## API Endpoints

```bash
# Health Check
curl http://localhost:8082/health

# List Orders
curl http://localhost:8082/order/index

# Create Order
curl -X POST http://localhost:8082/order/create \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "total_amount": 99.99,
    "items": [
      {"id": 1, "name": "Product 1", "price": 49.99, "quantity": 1}
    ]
  }'

# Monitoring Metrics
curl http://localhost:8082/monitoring/metrics

# FPM Status
curl http://localhost:8082/monitoring/fpm-status
```

---

**Note:** Make sure all containers are running:
```bash
docker-compose ps
```

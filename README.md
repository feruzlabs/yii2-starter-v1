# Yii2 Advanced Template with Docker

Complete Yii2 Advanced Template starter kit with Docker support, including API protection, monitoring, and microservices architecture.

## 🚀 Features

- **Yii2 Advanced Template** with API application
- **Docker-based** development and production environment
- **PHP 8.3** with PHP-FPM
- **Nginx** web server with optimized configuration
- **PostgreSQL 15** database
- **Redis 7** for caching and sessions
- **RabbitMQ 3.12** message broker
- **Supervisor** for background workers
- **API Protection:**
  - Priority-based rate limiting (Token Bucket)
  - Adaptive throttling (CPU/Memory based)
  - Circuit breaker pattern
  - Load shedding
- **Monitoring:**
  - PHP-FPM metrics
  - Application metrics
  - Alert system (Telegram, Email, Slack)
  - Health checks
- **Outbox Pattern** for reliable event publishing
- **Prometheus & Grafana** support (optional)

## 📋 Prerequisites

- Docker 20.10+
- Docker Compose 2.0+
- Make (optional, for convenience commands)

## 🛠️ Quick Start

### 1. Clone and Initialize

```bash
# Clone the repository
git clone <repository-url>
cd yii2-advanced-docker

# Copy environment file
cp .env.example .env

# Edit .env file with your configuration
nano .env

# Initialize and start services
make init
make up
```

Or without Make:

```bash
cp .env.example .env
docker-compose build
docker-compose up -d
docker-compose exec php-fpm php yii migrate --interactive=0
```

### 2. Access Services

- **Frontend:** http://localhost:8080
- **Backend:** http://localhost:8081
- **API:** http://localhost:8082
- **API Health:** http://localhost:8082/health
- **RabbitMQ Management:** http://localhost:15672 (guest/guest)
- **Prometheus:** http://localhost:9090 (with monitoring profile)
- **Grafana:** http://localhost:3000 (admin/admin, with monitoring profile)

### 3. Health Check

```bash
curl http://localhost:8082/health
```

## 📁 Project Structure

```
yii2-advanced-docker/
├── api/                    # API application
│   ├── config/            # API configuration
│   ├── controllers/       # API controllers
│   └── web/               # Web root
├── common/                # Shared components
│   ├── components/        # Protection components
│   ├── behaviors/         # Behaviors
│   ├── models/           # Models
│   └── config/           # Common config
├── console/              # Console commands
│   └── controllers/      # Workers
├── docker/               # Docker configuration
│   ├── php-fpm/         # PHP-FPM config
│   ├── nginx/           # Nginx config
│   ├── postgresql/      # PostgreSQL config
│   ├── redis/           # Redis config
│   └── rabbitmq/        # RabbitMQ config
├── migrations/           # Database migrations
├── scripts/             # Utility scripts
├── docker-compose.yml   # Main compose file
├── Makefile            # Convenience commands
└── README.md
```

## 🔧 Configuration

### Environment Variables

Key environment variables (see `.env.example` for full list):

```env
# Database
DB_DSN=pgsql:host=pgsql;dbname=yii2advanced
DB_USERNAME=yii2
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest

# Rate Limiting
RATE_LIMIT_USER_CAPACITY=100
RATE_LIMIT_USER_REFILL_RATE=10

# Throttling
THROTTLE_CPU_THRESHOLD=70
THROTTLE_MEMORY_THRESHOLD=80

# Alerts
ALERT_TELEGRAM_TOKEN=your_token
ALERT_TELEGRAM_CHAT_ID=your_chat_id
ALERT_EMAILS=admin@example.com
```

### Alert Configuration

Edit `common/config/alerts.yml` to configure alert rules:

```yaml
alerts:
  fpm_warning:
    severity: warning
    channels:
      - log
      - telegram
    throttle: 300  # seconds
```

## 🏃 Running Workers

Workers are automatically started by Supervisor in the PHP-FPM container:

- **Outbox Processor** (2 instances) - Publishes events to RabbitMQ
- **FPM Monitor** - Monitors PHP-FPM health
- **Metrics Collector** - Collects application metrics
- **Alert Checker** - Checks alert conditions

View worker logs:
```bash
make logs-workers
# or
docker-compose exec php-fpm tail -f /var/log/supervisor/*.log
```

## 📊 Monitoring

### View Metrics

```bash
curl http://localhost/api/monitoring/metrics
```

### View FPM Status

```bash
curl http://localhost/api/monitoring/fpm-status
```

### Alert History

```bash
docker-compose exec php-fpm php yii alert/history
```

## 🧪 Testing

### Create Test Order

```bash
curl -X POST http://localhost:8082/order/create \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "total_amount": 99.99,
    "items": [
      {"id": 1, "name": "Product 1", "price": 49.99, "quantity": 1},
      {"id": 2, "name": "Product 2", "price": 50.00, "quantity": 1}
    ]
  }'
```

### List Orders

```bash
curl http://localhost:8082/order/index
```

### Test Rate Limiting

```bash
for i in {1..100}; do curl http://localhost:8082/order/index; done
```

## 🐳 Docker Commands

### Using Makefile

```bash
make help              # Show available commands
make up                # Start all services
make down              # Stop all services
make restart           # Restart all services
make logs              # Tail all logs
make shell             # Shell into PHP container
make migrate           # Run migrations
make composer-install  # Install dependencies
make monitoring        # Start with monitoring stack
```

### Using Docker Compose Directly

```bash
docker-compose up -d              # Start services
docker-compose down               # Stop services
docker-compose ps                 # List services
docker-compose logs -f            # Tail logs
docker-compose exec php-fpm sh    # Shell into PHP
```

## 🔨 Development

### Run Migrations

```bash
make migrate
# or
docker-compose exec php-fpm php yii migrate
```

### Create Migration

```bash
make migrate-create name=create_users_table
```

### Install Dependencies

```bash
make composer-install
# or
docker-compose exec php-fpm composer install
```

### Access Database

```bash
make shell-db
# or
docker-compose exec pgsql psql -U yii2 -d yii2advanced
```

## 📈 Production Deployment

### 1. Update Environment

```bash
cp .env.example .env.production
# Edit .env.production with production settings
```

### 2. Build Production Images

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build
```

### 3. Deploy

```bash
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### 4. Security Checklist

- [ ] Change all default passwords
- [ ] Configure HTTPS/SSL certificates
- [ ] Set proper file permissions
- [ ] Configure firewall rules
- [ ] Enable security headers in Nginx
- [ ] Set up log rotation
- [ ] Configure backup strategy
- [ ] Set up monitoring alerts

## 🔍 Troubleshooting

### Services Won't Start

```bash
# Check logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database Connection Issues

```bash
# Check database is running
docker-compose ps pgsql

# Test connection
docker-compose exec php-fpm php yii migrate --interactive=0
```

### Permission Issues

```bash
# Fix permissions
docker-compose exec php-fpm chown -R www:www /var/www
```

### Worker Not Running

```bash
# Check supervisor status
docker-compose exec php-fpm supervisorctl status

# Restart workers
docker-compose exec php-fpm supervisorctl restart all
```

## 📚 Documentation

- [Yii2 Framework](https://www.yiiframework.com/doc/guide/2.0/en)
- [Docker Documentation](https://docs.docker.com/)
- [RabbitMQ Tutorials](https://www.rabbitmq.com/getstarted.html)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the BSD-3-Clause License.

## 🙏 Acknowledgments

- Yii2 Framework Team
- Docker Community
- All contributors

---

**Built with ❤️ using Yii2 and Docker**

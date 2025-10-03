# 🚀 Production Deployment Guide

This branch (`prod`) contains production-ready configuration for the Yii2 Advanced Template.

---

## 📋 What's Different in Production?

### 1. **Docker Compose Configuration**

#### Ports
- **Frontend**: Port `80` (instead of `8080`)
- **Backend**: Port `81` (instead of `8081`)
- **API**: Port `82` (instead of `8082`)
- **HTTPS**: Port `443` enabled

#### Security
- All code volumes mounted as **read-only** (`:ro`)
- Internal services use `expose` instead of `ports`
- Database backup volume added
- Session storage volume added

#### Performance
- Resource limits configured for all services
- `restart: always` policy for automatic recovery
- Optimized health checks

### 2. **PHP Configuration**

#### OPcache (Production Mode)
```ini
opcache.validate_timestamps = 0      # Don't check file changes
opcache.memory_consumption = 512M    # Increased memory
opcache.max_accelerated_files = 20000
opcache.save_comments = 0            # Strip comments
opcache.fast_shutdown = 1
```

#### PHP-FPM Pool
```ini
pm.max_children = 100               # More workers
pm.start_servers = 10
pm.min_spare_servers = 10
pm.max_spare_servers = 30
pm.max_requests = 1000
request_terminate_timeout = 60s
```

#### Error Handling
```ini
display_errors = Off
error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED
report_memleaks = Off
```

### 3. **Nginx Configuration**

#### Security Headers
- HSTS enabled (`Strict-Transport-Security`)
- Server tokens hidden (`server_tokens off`)
- XSS Protection enabled
- MIME type sniffing blocked

#### Performance
- Gzip compression enabled
- FastCGI cache configured
- Rate limiting active
- Keepalive connections optimized

### 4. **Environment Variables**

Copy `.env.production` to `.env` and update:

```bash
# CRITICAL: Change all passwords!
DB_PASSWORD=CHANGE_ME_STRONG_PASSWORD
RABBITMQ_PASSWORD=CHANGE_ME_RABBITMQ_PASSWORD
GRAFANA_PASSWORD=CHANGE_ME_GRAFANA_PASSWORD
APP_SECRET_KEY=CHANGE_ME_SECRET_KEY_32_CHARS_MIN
SMTP_PASSWORD=CHANGE_ME_SMTP_PASSWORD
```

---

## 🔧 Deployment Steps

### 1. **Clone Repository**
```bash
git clone https://github.com/feruzlabs/yii2-starter-v1.git
cd yii2-starter-v1
git checkout prod
```

### 2. **Configure Environment**
```bash
# Copy and edit production environment
cp .env.production .env
nano .env  # Update all CHANGE_ME values
```

### 3. **Install Dependencies**
```bash
docker-compose run --rm php-fpm composer install --no-dev --optimize-autoloader
```

### 4. **Run Migrations**
```bash
docker-compose run --rm php-fpm php yii migrate --interactive=0
```

### 5. **Build and Start**
```bash
# Build images
docker-compose build

# Start services
docker-compose up -d

# Check status
docker-compose ps
```

### 6. **Verify Deployment**
```bash
# Test frontend
curl http://your-domain.com

# Test backend
curl http://your-domain.com:81

# Test API health
curl http://your-domain.com:82/health
```

---

## 🔒 Security Checklist

- [ ] Change all default passwords in `.env`
- [ ] Configure SSL/TLS certificates for HTTPS
- [ ] Set up firewall rules (ports 80, 81, 82, 443 only)
- [ ] Enable database backups (see below)
- [ ] Configure monitoring alerts
- [ ] Review and restrict RabbitMQ management UI access (port 15672)
- [ ] Set up log rotation for nginx and PHP-FPM logs

---

## 💾 Database Backups

### Manual Backup
```bash
docker-compose exec pgsql pg_dump -U yii2 yii2advanced > backup_$(date +%Y%m%d).sql
```

### Automated Backup (Cron)
```bash
# Add to crontab
0 2 * * * cd /path/to/project && docker-compose exec -T pgsql pg_dump -U yii2 yii2advanced | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz
```

### Restore
```bash
docker-compose exec -T pgsql psql -U yii2 yii2advanced < backup.sql
```

---

## 📊 Monitoring

### Service Health
```bash
# Check all services
docker-compose ps

# View logs
docker-compose logs -f php-fpm
docker-compose logs -f nginx

# PHP-FPM status
curl http://localhost:82/fpm-status

# API health
curl http://localhost:82/health
```

### Metrics (Optional - Enable Prometheus/Grafana)
```bash
# Start with monitoring stack
docker-compose --profile monitoring up -d

# Access Grafana
http://your-domain.com:3000
```

---

## 🔄 Updates and Maintenance

### Update Code
```bash
git pull origin prod
docker-compose run --rm php-fpm composer install --no-dev --optimize-autoloader
docker-compose run --rm php-fpm php yii migrate --interactive=0
docker-compose restart php-fpm
```

### Clear OPcache (After Code Update)
```bash
docker-compose exec php-fpm kill -USR2 1
```

### View Resource Usage
```bash
docker stats
```

---

## 🚨 Troubleshooting

### Container Won't Start
```bash
# Check logs
docker-compose logs <service_name>

# Rebuild
docker-compose build --no-cache <service_name>
docker-compose up -d <service_name>
```

### Permission Issues
```bash
# Fix permissions for runtime directories
docker-compose exec php-fpm chmod -R 777 /var/www/*/runtime
docker-compose exec php-fpm chmod -R 777 /var/www/*/web/assets
```

### Database Connection Failed
```bash
# Verify database is running
docker-compose exec pgsql pg_isready -U yii2

# Check credentials in .env
```

---

## 📝 Key Differences from Development

| Feature | Development | Production |
|---------|-------------|-----------|
| **Ports** | 8080, 8081, 8082 | 80, 81, 82 |
| **Code Volumes** | Read-Write | Read-Only |
| **OPcache** | Validate timestamps | No validation |
| **Debug Mode** | Enabled | Disabled |
| **Error Display** | On | Off |
| **Xdebug** | Available | Disabled |
| **Restart Policy** | unless-stopped | always |
| **Composer** | With dev deps | Production only |
| **Service Exposure** | All ports exposed | Internal only |

---

## 🌐 DNS Configuration

Point your domain to the server:

```
A     example.com          -> YOUR_SERVER_IP
A     www.example.com      -> YOUR_SERVER_IP
A     api.example.com      -> YOUR_SERVER_IP
A     admin.example.com    -> YOUR_SERVER_IP
```

---

## 📞 Support

For issues or questions:
- GitHub Issues: https://github.com/feruzlabs/yii2-starter-v1/issues
- Documentation: See README.md, APPLICATIONS.md, PORTS.md

---

**🔐 Remember: Security is your responsibility. Always keep your system updated and monitor for suspicious activity.**

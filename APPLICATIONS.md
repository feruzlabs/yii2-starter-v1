# 📱 Applications Overview

This Yii2 Advanced Template includes **3 separate applications** running on different ports:

---

## 🌐 Frontend (Public Website)

**URL:** http://localhost:8080  
**Port:** 8080  
**Purpose:** Public-facing website

### Features:
- ✅ Homepage with system overview
- ✅ About page
- ✅ Modern gradient design
- ✅ Links to all services
- ✅ System status display

### Location:
```
frontend/
├── config/          # Configuration files
├── controllers/     # Controllers (SiteController)
├── views/           # View templates
│   ├── layouts/    # Layout templates
│   └── site/       # Site views (index, about)
└── web/            # Web root
    └── index.php   # Entry point
```

### Test:
```bash
curl http://localhost:8080
```

---

## 🔧 Backend (Admin Panel)

**URL:** http://localhost:8081  
**Port:** 8081  
**Purpose:** Admin panel and management system

### Features:
- ✅ Admin dashboard
- ✅ System statistics
- ✅ Service health checks
- ✅ Quick access links
- ✅ Connection status for DB, Redis, RabbitMQ

### Location:
```
backend/
├── config/          # Configuration files
├── controllers/     # Controllers (SiteController)
├── views/           # View templates
│   ├── layouts/    # Layout templates
│   └── site/       # Site views (index)
└── web/            # Web root
    └── index.php   # Entry point
```

### Test:
```bash
curl http://localhost:8081
```

---

## ⚡ API (REST API)

**URL:** http://localhost:8082  
**Port:** 8082  
**Purpose:** REST API with protection features

### Features:
- ✅ Health check endpoint
- ✅ Order management (CRUD)
- ✅ Monitoring endpoints
- ✅ Rate limiting (priority-based)
- ✅ Adaptive throttling
- ✅ Circuit breaker pattern
- ✅ Metrics collection

### Location:
```
api/
├── config/          # Configuration files
├── controllers/     # API Controllers
│   ├── HealthController.php
│   ├── MonitoringController.php
│   └── OrderController.php
└── web/            # Web root
    └── index.php   # Entry point
```

### Endpoints:

#### Health Check
```bash
curl http://localhost:8082/health
```

#### Create Order
```bash
curl -X POST http://localhost:8082/order/create \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "total_amount": 99.99,
    "items": [
      {"id": 1, "name": "Product 1", "price": 49.99, "quantity": 1}
    ]
  }'
```

#### List Orders
```bash
curl http://localhost:8082/order/index
```

#### Monitoring Metrics
```bash
curl http://localhost:8082/monitoring/metrics
```

#### FPM Status
```bash
curl http://localhost:8082/monitoring/fpm-status
```

---

## 🔗 Shared Components

All applications share the following:

### Common Directory
```
common/
├── components/      # Protection components
│   ├── PriorityRateLimiter.php
│   ├── AdaptiveThrottler.php
│   ├── CircuitBreaker.php
│   ├── MetricsCollector.php
│   ├── PhpFpmMonitor.php
│   ├── AlertManager.php
│   └── RabbitMQComponent.php
├── behaviors/       # Behaviors
│   └── ApiProtectionBehavior.php
├── models/          # Models
│   ├── User.php
│   ├── Order.php
│   └── OutboxMessage.php
└── config/          # Common configuration
```

### Console Application
```
console/
└── controllers/     # Background workers
    ├── OutboxProcessorController.php
    ├── FpmMonitorController.php
    ├── MetricsController.php
    └── AlertController.php
```

---

## 🎨 Design & Styling

Each application has its own color theme:

| Application | Primary Color | Gradient |
|------------|---------------|----------|
| **Frontend** | Purple (#667eea) | Purple to Dark Purple |
| **Backend** | Pink (#f5576c) | Pink to Purple |
| **API** | Blue (#4facfe) | Blue to Cyan |

---

## 🧪 Testing All Applications

```bash
# Test Frontend
curl -I http://localhost:8080

# Test Backend
curl -I http://localhost:8081

# Test API
curl http://localhost:8082/health

# Test all at once
echo "Frontend:" && curl -s http://localhost:8080 | grep -o "<title>.*</title>" && \
echo "Backend:" && curl -s http://localhost:8081 | grep -o "<title>.*</title>" && \
echo "API:" && curl -s http://localhost:8082/health
```

---

## 📊 Port Summary

| Application | Port | Description |
|------------|------|-------------|
| Frontend | 8080 | Public website |
| Backend | 8081 | Admin panel |
| API | 8082 | REST API |
| PostgreSQL | 5432 | Database |
| Redis | 6379 | Cache/Session |
| RabbitMQ | 5672 | Message Queue |
| RabbitMQ UI | 15672 | Management |
| PHP-FPM Status | 9001 | FPM monitoring |

---

**All applications are fully functional and ready to use!** 🚀

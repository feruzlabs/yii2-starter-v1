.PHONY: help init up down restart logs shell migrate test composer-install clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

init: ## Initialize project
	@echo "Initializing project..."
	cp .env.example .env
	docker-compose build
	@echo "Project initialized. Edit .env file and run 'make up'"

up: ## Start all services
	docker-compose up -d
	@echo "Services started. Waiting for containers to be ready..."
	sleep 10
	@echo "Running migrations..."
	docker-compose exec php-fpm php yii migrate --interactive=0
	@echo "Done! Access the application at:"
	@echo "  Frontend: http://localhost"
	@echo "  API: http://localhost/api"
	@echo "  RabbitMQ: http://localhost:15672"

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## Tail all logs
	docker-compose logs -f

logs-api: ## Tail API logs
	docker-compose logs -f php-fpm nginx

logs-workers: ## Tail worker logs
	docker-compose exec php-fpm tail -f /var/log/supervisor/*.log

shell: ## Shell into php-fpm container
	docker-compose exec php-fpm sh

shell-db: ## Shell into database container
	docker-compose exec pgsql psql -U yii2 -d yii2advanced

migrate: ## Run migrations
	docker-compose exec php-fpm php yii migrate --interactive=0

migrate-create: ## Create new migration (name=migration_name)
	docker-compose exec php-fpm php yii migrate/create $(name)

test: ## Run tests
	docker-compose exec php-fpm php vendor/bin/codecept run

composer-install: ## Install composer dependencies
	docker-compose exec php-fpm composer install

composer-update: ## Update composer dependencies
	docker-compose exec php-fpm composer update

clean: ## Clean up containers and volumes
	docker-compose down -v
	rm -rf vendor/

ps: ## Show running containers
	docker-compose ps

status: ## Show service status
	@echo "=== Docker Containers ==="
	@docker-compose ps
	@echo ""
	@echo "=== Health Checks ==="
	@curl -s http://localhost/health | jq . || echo "API not available"

monitoring: ## Start with monitoring stack
	docker-compose --profile monitoring up -d

stop-monitoring: ## Stop monitoring stack
	docker-compose --profile monitoring down

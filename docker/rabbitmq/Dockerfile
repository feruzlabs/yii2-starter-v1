FROM rabbitmq:3.12-management-alpine

# Install additional tools
RUN apk add --no-cache \
    bash \
    curl

# Copy configuration
COPY rabbitmq.conf /etc/rabbitmq/rabbitmq.conf
COPY enabled_plugins /etc/rabbitmq/enabled_plugins

# Create directories
RUN mkdir -p /var/lib/rabbitmq \
    && chown -R rabbitmq:rabbitmq /var/lib/rabbitmq

# Expose ports
EXPOSE 5672 15672

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD rabbitmq-diagnostics ping || exit 1

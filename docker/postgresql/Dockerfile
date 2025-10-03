FROM postgres:15-alpine

# Install additional tools
RUN apk add --no-cache \
    bash \
    curl

# Create init directory
RUN mkdir -p /docker-entrypoint-initdb.d

# Set environment variables
ENV POSTGRES_DB=yii2advanced
ENV POSTGRES_USER=yii2
ENV POSTGRES_PASSWORD=secret

# Expose port
EXPOSE 5432

# Health check
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
    CMD pg_isready -U $POSTGRES_USER -d $POSTGRES_DB || exit 1

FROM nginx:alpine

# Install curl for health checks
RUN apk add --no-cache curl

# Remove default config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy site configurations
COPY api.conf /etc/nginx/conf.d/api.conf
COPY backend.conf /etc/nginx/conf.d/backend.conf
COPY frontend.conf /etc/nginx/conf.d/frontend.conf

# Create log directory
RUN mkdir -p /var/log/nginx

# Set permissions
RUN chown -R nginx:nginx /var/log/nginx

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:82/health || exit 1

CMD ["nginx", "-g", "daemon off;"]

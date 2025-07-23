# TimeEffect PHP 8.4 Docker Image
# Based on webdevops/php-apache:8.4 with TimeEffect-specific configurations

FROM webdevops/php-apache:8.4

# Set maintainer
LABEL maintainer="TimeEffect Team"
LABEL description="TimeEffect PHP 8.4 with Apache and MySQL support"

# Copy custom Apache VHost configuration
COPY vhost-fix.conf /opt/docker/etc/httpd/vhost.conf

# Enable PHP short tags
RUN echo "short_open_tag = On" >> /usr/local/etc/php/conf.d/timeeffect.ini

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/logs \
    && mkdir -p /var/www/html/install/include \
    && chown -R application:application /var/www/html/logs \
    && chown -R application:application /var/www/html/install/include \
    && chmod 755 /var/www/html/logs \
    && chmod 755 /var/www/html/install/include

# Install additional PHP extensions if needed
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

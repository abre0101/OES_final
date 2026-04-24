FROM php:8.2-fpm

# Install mysqli and other required extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install nginx
RUN apt-get update && apt-get install -y nginx && rm -rf /var/lib/apt/lists/*

# Copy application files
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Copy start script and fix line endings
COPY docker/start.sh /start.sh
RUN sed -i 's/\r//' /start.sh && chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]

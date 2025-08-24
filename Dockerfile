FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    ca-certificates \
    gnupg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm -v && node -v

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Copy start script
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh && chown www:www /start.sh

# Expose php-fpm + Vite dev port
EXPOSE 9000 5173

# Switch user
USER www

ENV CHOKIDAR_USEPOLLING=true

CMD ["/start.sh"]

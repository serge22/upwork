# ============================================
# 1. Backend build (Composer)
# ============================================
FROM composer:2.7 AS build-backend
WORKDIR /app

# Copy only dependency files first
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy application code AFTER dependencies are installed
COPY . .
RUN composer dump-autoload --optimize

# ============================================
# 2. Frontend build (Node + Vite)
# ============================================
FROM node:20 AS build-frontend
WORKDIR /app

# Copy only package files first
COPY package.json package-lock.json ./
RUN npm ci

# Copy application code
COPY --from=build-backend /app ./
RUN npm run build

# ============================================
# 3. Production image (PHP + Nginx + S6 Overlay)
# ============================================
FROM php:8.4-fpm AS production

# --- Install s6-overlay ---
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.2.1.0/s6-overlay-noarch.tar.xz /tmp/
ADD https://github.com/just-containers/s6-overlay/releases/download/v3.2.1.0/s6-overlay-x86_64.tar.xz /tmp/

# Update package lists, install all necessary dependencies (runtime and build),
# extract s6-overlay, install PHP extensions, and clean up.
RUN apt-get update \
    # 1. Install all dependencies:
    # - Runtime dependencies (nginx, xz-utils, libicu-dev, libmariadb-dev-compat)
    # - Build dependencies ($PHPIZE_DEPS) for compiling PHP extensions
    && apt-get install -y --no-install-recommends \
        nginx \
        libicu-dev \
        $PHPIZE_DEPS \
    # Cleanup apt lists immediately to reduce image size
    && rm -rf /var/lib/apt/lists/* \
    \
    # 2. Extract s6-overlay
    && tar -C / -Jxpf /tmp/s6-overlay-noarch.tar.xz \
    && tar -C / -Jxpf /tmp/s6-overlay-x86_64.tar.xz \
    \
    # 3. Configure and install PHP extensions
    # Use -j$(nproc) to speed up compilation
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql opcache \
    \
    # 4. Cleanup build dependencies and temporary files
    # Only purge PHPIZE_DEPS as others (nginx, libicu-dev, etc.) are required at runtime
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /tmp/s6-overlay-*.tar.xz

WORKDIR /var/www/html

# Copy config files first (these change less frequently)
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/s6-rc.d /etc/s6-overlay/s6-rc.d/

RUN chmod +x /etc/s6-overlay/s6-rc.d/*/run \
    && chmod +x /etc/s6-overlay/s6-rc.d/*/up

# Copy application code LAST
COPY --from=build-frontend --chown=www-data:www-data /app ./

EXPOSE 80

ENTRYPOINT ["/init"]
FROM php:8.2-apache

# ── System dependencies ───────────────────────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libwebp-dev \
        libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*

# ── PHP extensions ────────────────────────────────────────────────────────────
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" pdo pdo_mysql gd curl

# ── Apache config ─────────────────────────────────────────────────────────────
RUN a2enmod rewrite

# Cloud Run requires the container to listen on 8080
RUN sed -i 's/Listen 80$/Listen 8080/' /etc/apache2/ports.conf
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# ── Application files ─────────────────────────────────────────────────────────
WORKDIR /var/www/html
COPY . .

# Drop the server-specific config — runtime version takes its place
RUN rm -f config/config.php

# Install runtime config (reads all settings from environment variables)
COPY docker/config.runtime.php config/config.php

# Generate PWA icons at build time (GD only — no real DB/URL needed)
RUN mkdir -p public/icons \
 && php database/generate_icons.php 2>/dev/null || true

# ── Permissions ───────────────────────────────────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
 && find /var/www/html -type d -exec chmod 755 {} \; \
 && find /var/www/html -type f -exec chmod 644 {} \;

EXPOSE 8080
CMD ["apache2-foreground"]

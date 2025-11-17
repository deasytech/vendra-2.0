# Build stage for frontend assets
FROM php:8.3-cli-alpine AS builder

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nodejs \
    npm \
    libzip-dev \
    icu-dev \
    && docker-php-ext-install zip intl

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction

# Copy package files and install node dependencies
COPY package*.json ./
RUN npm install

# Copy source files and build
COPY . .
RUN npm run build

# Final nginx stage
FROM nginx:latest

# Copy Laravel application files
COPY . /var/www

# Copy built assets from builder stage
COPY --from=builder /app/public/build /var/www/public/build

# Copy nginx configuration
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www

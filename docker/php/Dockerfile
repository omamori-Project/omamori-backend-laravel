FROM php:8.3-fpm-alpine

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 빌드 의존성 및 런타임 패키지를 한 번에 설치
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    icu-dev \
    imagemagick-dev \
    imagemagick \
    bash \
    vim \
    unzip \
    libpng \
    libjpeg-turbo \
    freetype \
    libwebp \
    libzip \
    postgresql-libs \
    icu-libs

# 빌드 도구 설치 (임시)
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make \
    linux-headers

# PHP 확장 설치
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    gd \
    zip \
    bcmath \
    opcache \
    intl \
    exif

# ImageMagick PHP 확장 설치
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# Redis 확장 설치
RUN pecl install redis \
    && docker-php-ext-enable redis

# 빌드 도구 제거 (이미지 크기 최적화)
RUN apk del .build-deps

# Composer 설치
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP 설정 최적화
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/execution.ini

# 권한 설정
RUN addgroup -g 1000 www && \
    adduser -D -u 1000 -G www www

USER www

EXPOSE 9000

CMD ["php-fpm"]
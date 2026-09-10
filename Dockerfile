# Versione pinnata: stessa major/minor dell'ambiente documentato (PHP 8.2, XAMPP)
FROM php:8.2-apache

# Aggiorna i pacchetti e installa le estensioni di base per i database (PDO e MySQLi)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql mysqli gd

# Abilita il modulo rewrite di Apache (utile per i framework o file .htaccess)
RUN a2enmod rewrite
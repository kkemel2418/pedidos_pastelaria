# Use a imagem oficial do PHP 8.1 como imagem base
FROM php:8.1-apache

# Definir o diretório de trabalho dentro do contêiner
WORKDIR /var/www/html

# Copiar os arquivos da aplicação para o diretório de trabalho do contêiner
COPY . .

# Ajusta as permissões do diretório
RUN chown -R www-data:www-data .

# Instala as extensões do PHP necessárias para o Lumen
RUN docker-php-ext-install pdo pdo_mysql

# Permite que o Apache reescreva URLs (se necessário)
RUN a2enmod rewrite

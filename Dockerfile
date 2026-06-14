# Используем официальный образ PHP с Apache
FROM php:8.2-apache

# Обновляем список пакетов и устанавливаем системную библиотеку для PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev

# Устанавливаем и включаем расширения PHP для работы с БД Supabase (PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Включаем модуль rewrite для корректной работы ссылок и AJAX
RUN a2enmod rewrite

# Копируем содержимое твоей папки htdocs в главную папку сервера
COPY htdocs/ /var/www/html/

# Выставляем правильные права доступа, чтобы сайт работал без ошибок
RUN chown -R www-data:www-data /var/www/html

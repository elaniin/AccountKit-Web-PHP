FROM php:5.6-apache
RUN apt-get update -y && apt-get install -y vim
COPY config/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY config/ports.conf /etc/apache2/ports.conf
WORKDIR /var/www/html/
ADD . .
EXPOSE 8080
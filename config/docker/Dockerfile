FROM jerfeson/npf:php74
ARG DEBIAN_FRONTEND=noninteractive

COPY nginx/sites-enabled/default /etc/nginx/sites-available/default
COPY nginx/fastcgi-php.conf /etc/nginx/snippets/fastcgi-php.conf
COPY php/php.ini /etc/php/7.4/fpm/php.ini

RUN apt-get update && apt-get -y upgrade && apt-get -y dist-upgrade
RUN apt-get -y install php7.4-sqlite3
RUN curl --insecure https://getcomposer.org/composer.phar -o /usr/bin/composer && chmod +x /usr/bin/composer

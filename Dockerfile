FROM webdevops/php-nginx:alpine-php7

WORKDIR /app

COPY vhost.conf /opt/docker/etc/nginx
COPY composer.lock composer.json /app/
COPY . /app

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin && rm composer-setup.php
USER application
RUN composer.phar install --no-dev --no-scripts
USER root
RUN rm /usr/local/bin/composer.phar

RUN chown -R application:application /app

RUN php artisan optimize

RUN crontab -l | { cat; echo "0    0    *     *     *    su -c 'cd /app && php artisan airport:cache' application"; } | crontab -
RUN crontab -l | { cat; echo "@reboot su -c 'cd /app && php artisan airport:cache' application"; } | crontab -

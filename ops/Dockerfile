FROM ubuntu:20.04
RUN apt-get update -y
RUN apt-get install nginx -y
RUN apt-get install software-properties-common -y
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update -y
RUN apt-get install -y php7.0 php7.0-cli php7.0-json php7.0-common php7.0-mysql php7.0-zip php7.0-gd php7.0-mbstring \
            php7.0-curl php7.0-xml php7.0-bcmath php7.0-fpm php7.0-xdebug
RUN apt-get install -y curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=1.10.16


#fpm conf
COPY conf/www.conf /etc/php/7.0/fpm/pool.d/www.conf

#nginx site conf
RUN mkdir -p /etc/nginx/sites-enabled
RUN rm /etc/nginx/sites-available/default
RUN rm /etc/nginx/fastcgi_params
COPY conf/rozz.conf  /etc/nginx/sites-available/default
COPY conf/rozz.conf  /etc/nginx/sites-enabled/default
COPY conf/fastcgi_params /etc/nginx/fastcgi_params

EXPOSE 80
WORKDIR /rozz/

#start services
RUN mkdir -p /run/php
CMD nginx && php-fpm7.0 -F
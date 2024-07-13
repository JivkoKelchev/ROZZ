FROM nginx:1.23.2

ENV DEBIAN_FRONTEND noninteractive

# Update the package repository
RUN apt-get -qq update
# Install fpm php and tools
RUN apt-get -y install unzip
RUN	apt-get install -y php7.4-cli php7.4-json php7.4-common php7.4-mysql php7.4-zip php7.4-gd php7.4-mbstring \
    php7.4-curl php7.4-xml php7.4-bcmath php7.4-fpm php7.4-xdebug\
	libcurl4-gnutls-dev \
	zlib1g zlib1g-dev \
	openssl vim curl net-tools

#xdebug conf
RUN echo "zend_extension=$(find /usr/lib -name xdebug.so)" > /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "; Xdebug" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.mode=develop,coverage,debug" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.log_level=0" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /etc/php/7.4/cli/conf.d/20-xdebug.ini


#fpm conf
COPY conf/www.conf /etc/php/7.4/fpm/pool.d/www.conf
#nginx general conf
COPY conf/nginx.conf /etc/nginx/nginx.conf
#nginx site conf
RUN mkdir -p /etc/nginx/sites-enabled
COPY conf/rozz.conf  /etc/nginx/sites-enabled/rozz.conf

EXPOSE 80
WORKDIR /rozz/

#start services
RUN mkdir -p /run/php
CMD nginx && php-fpm7.4 -F
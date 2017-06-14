FROM php:7.1-fpm

# PHP
RUN apt-get update -qq && apt-get install -y git sudo libcurl4-openssl-dev \
	&& apt-get install -y libmcrypt-dev && docker-php-ext-install mcrypt \
	&& apt-get install -y libpq-dev && docker-php-ext-install pdo_pgsql \
	&& apt-get install -y zip unzip && docker-php-ext-install zip \
	&& apt-get install -y libxslt-dev && docker-php-ext-install xsl

# COMPOSER
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# NODE JS
RUN curl -sL https://deb.nodesource.com/setup_7.x | sudo -E bash - \
	&& apt-get install -y nodejs \
	&& ln -sf /usr/bin/nodejs /usr/bin/node

# YARN & GULP
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
	&& echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list \
	&& apt-get update && apt-get install yarn
RUN yarn global add gulp

# CLEANING
RUN apt-get clean
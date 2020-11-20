FROM ubuntu:latest
MAINTAINER David Salac

# Install standard requirements for PHP 7.4
RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get -y install \
    apache2 php7.4 php7.4-mysql libapache2-mod-php7.4 curl lynx

# Enable apache mods.
RUN a2enmod php7.4
RUN a2enmod rewrite

# Update the PHP.ini file, enable short <? ?> tags and quieten logging.
RUN sed -i "s/short_open_tag = Off/short_open_tag = On/" /etc/php/7.4/apache2/php.ini
RUN sed -i "s/error_reporting = .*$/error_reporting = E_PARSE/" /etc/php/7.4/apache2/php.ini

# Manually set up the apache environment variables
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Expose apache.
EXPOSE 80

# Update the default apache site with the config we created.
ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf

# !!!! IMPORTANT !!!!
# Sites are in the folder: /var/www/site

# By default start up apache in the foreground
CMD /usr/sbin/apache2ctl -D FOREGROUND
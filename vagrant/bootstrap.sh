#!/usr/bin/env bash

echo -e "\nUpdating APT packages..."
sudo apt-get update -qq > /dev/null

# Install Git
if [ ! -f "/usr/bin/git" ]; then
	echo -e "\nInstalling GIT..."
	sudo apt-get install git -qq -y --force-yes > /dev/null
fi

# Install Apache
if [ ! -d "/etc/apache2" ]; then
	echo -e "\nInstalling Apache..."
	sudo apt-get install apache2 -qq -y --force-yes > /dev/null
	sudo a2enmod rewrite > /dev/null
fi

# Install Site virtual host
if [ ! -d /var/www/cloud-deploy ]; then
	echo -e "\nInstalling cloud-deploy..."
	sudo ln -s /vagrant /var/www/cloud-deploy
	sudo ln -s /vagrant/vagrant/config_files/apache2/cloud-deploy.conf /etc/apache2/sites-enabled/cloud-deploy.conf
	sudo service apache2 restart > /dev/null
fi

# Install PHP
if [ ! -d "/etc/php5" ]; then
	echo -e "\nInstalling PHP..."
	sudo apt-get install php5 php5-dev php-pear php5-apcu php5-curl php5-xdebug php5-intl php5-mysqlnd php5-mcrypt -qq -y --force-yes > /dev/null
	sudo service apache2 restart > /dev/null
	echo "suhosin.executor.include.whitelist = phar" | sudo tee -a /etc/php5/cli/php.ini > /dev/null
	echo "date.timezone = Australia/Melbourne" | sudo tee -a /etc/php5/cli/php.ini > /dev/null
	echo "suhosin.executor.include.whitelist = phar" | sudo tee -a /etc/php5/apache2/php.ini > /dev/null
	echo "date.timezone = Australia/Melbourne" | sudo tee -a /etc/php5/apache2/php.ini > /dev/null
fi

# Install MySQL Server
if [ ! -d "/etc/mysql" ]; then
	echo -e "\nInstalling MySQL Server..."
	sudo export DEBIAN_FRONTEND=noninteractive
	sudo echo mysql-server mysql-server/root_password password "password" | sudo debconf-set-selections > /dev/null
	sudo echo mysql-server mysql-server/root_password_again password "password" | sudo debconf-set-selections > /dev/null
	sudo apt-get install mysql-server-5.5 -qq -y > /dev/null

	echo -e "\nCreating databases..."
	echo "CREATE database 'cloud-deploy'" | mysql -uroot -pP@55w0rd > /dev/null
	echo "GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.11.%' IDENTIFIED BY 'password';"  | mysql -uroot -ppassword > /dev/null
	echo "GRANT SUPER ON *.* TO 'root'@'192.168.11.%' IDENTIFIED BY 'password';"  | mysql -uroot -ppassword > /dev/null
fi

# Install MySQL client
if [ ! -f "/usr/bin/mysql" ]; then
	echo -e "\nInstalling MySQL Client..."
	sudo apt-get install mysql-client-5.5 -qq -y > /dev/null
fi

# Install curl
if [ ! -f "/usr/bin/curl" ]; then
	echo -e "\nInstalling cUrl..."
	sudo apt-get install curl -y > /dev/null
fi

# Install zip/unzip
if [ ! -f "/usr/bin/unzip" ]; then
	echo -e "\nInstalling zip/unzip..."
	sudo apt-get install unzip zip -qq -y > /dev/null
fi

# Install PHPMyAdmin
if [ ! -d /var/www/phpmyadmin ]; then
	echo -e "\nInstalling phpMyAdmin...";
	sudo mkdir /var/www/phpmyadmin
	sudo chown vagrant:www-data /var/www/phpmyadmin

	sudo wget -q -O /var/www/phpmyadmin/phpmyadmin.zip "http://downloads.sourceforge.net/project/phpmyadmin/phpMyAdmin/4.0.6/phpMyAdmin-4.0.6-english.zip" > /dev/null
	cd /var/www/phpmyadmin
	sudo unzip -qq /var/www/phpmyadmin/phpmyadmin.zip > /dev/null
	sudo mv /var/www/phpmyadmin/phpMyAdmin-4.0.6-english/* .
	sudo rm /var/www/phpmyadmin/phpMyAdmin-4.0.6-english phpmyadmin.zip -rf

	sudo chown vagrant:www-data /var/www/phpmyadmin -R

	sudo ln -s /vagrant/vagrant/config_files/apache2/phpmyadmin.conf /etc/apache2/sites-enabled/phpmyadmin.conf
	sudo service apache2 restart > /dev/null
fi

# Install PHPUnit
if [ ! -f /usr/local/bin/phpunit ]; then
	echo -e "\nInstalling phpunit..."
	sudo wget -q -O /usr/local/bin/phpunit http://pear.phpunit.de/get/phpunit.phar > /dev/null
	sudo chmod +x /usr/local/bin/phpunit
fi

# Install SendMail
if [ ! -f  /usr/sbin/sendmail ]; then
	echo -e "\nInstalling sendmail..."
	#sudo apt-get install sendmail -qq -y > /dev/null
fi


sudo service mysql restart > /dev/null
sudo service apache2 restart > /dev/null

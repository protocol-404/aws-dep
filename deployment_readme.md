# Laravel Deployment Guide

This guide provides step-by-step instructions for deploying a Laravel PHP 8.3 application on an Ubuntu EC2 instance. Two web server options are included: Nginx and Apache.

## Prerequisites
- Ubuntu EC2 instance running
- Access to AWS RDS for your database
- Your Laravel project code (in a git repository or otherwise transferable)

## Nginx Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```
*Purpose: Updates package lists and upgrades installed packages to ensure security and compatibility.*

### 2. Install Required Packages
```bash
# Install Nginx
sudo apt install nginx -y

# Add PHP repository
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 and extensions
sudo apt install php8.3 php8.3-fpm php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-mysql php8.3-intl php8.3-gd -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Git
sudo apt install git -y
```
*Purpose: Installs the Nginx web server, PHP 8.3 with required extensions for Laravel, Composer for PHP dependency management, and Git for code version control.*

### 3. Configure Nginx
```bash
sudo nano /etc/nginx/sites-available/laravel
```

Add this configuration:
```nginx
server {
    listen 80;
    server_name your_domain_or_public_ip;
    root /var/www/laravel/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
*Purpose: Creates a Nginx server block that handles HTTP requests, routes them to the correct PHP-FPM process, and sets security headers.*

### 4. Enable the Site
```bash
sudo ln -s /etc/nginx/sites-available/laravel /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```
*Purpose: Creates a symbolic link to enable the site, removes the default site, tests the configuration for syntax errors, and restarts Nginx to apply changes.*

### 5. Deploy Laravel Application
```bash
# Create web directory
sudo mkdir -p /var/www/laravel

# Clone your repository
sudo git clone https://github.com/yourusername/your-repo.git /var/www/laravel
# OR copy your files
# sudo cp -r /path/to/your/project/* /var/www/laravel/

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel
sudo chmod -R 755 /var/www/laravel
sudo chmod -R 775 /var/www/laravel/storage
sudo chmod -R 775 /var/www/laravel/bootstrap/cache
```
*Purpose: Creates the web directory, clones your Laravel application code, and sets proper ownership and permissions so the web server can read/write necessary files.*

### 6. Configure Laravel
```bash
cd /var/www/laravel

# Copy environment file
sudo cp .env.example .env
sudo nano .env
```

Update the .env with your RDS details:
```
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

```bash
# Install dependencies
sudo composer install --no-dev --optimize-autoloader

# Generate key
sudo php artisan key:generate

# Cache configuration
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```
*Purpose: Sets up Laravel's environment configuration, installs production dependencies, generates an application key, and caches configurations for better performance.*

### 7. Verify Installation
Visit your server's IP address or domain in your browser to confirm your Laravel application is running correctly.

## Apache Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
```
*Purpose: Updates package lists and upgrades installed packages to ensure security and compatibility.*

### 2. Install Required Packages
```bash
# Install Apache
sudo apt install apache2 -y

# Add PHP repository
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 and extensions
sudo apt install php8.3 libapache2-mod-php8.3 php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-mysql php8.3-intl php8.3-gd -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Git
sudo apt install git -y
```
*Purpose: Installs the Apache web server, PHP 8.3 with required extensions for Laravel, Composer for PHP dependency management, and Git for code version control.*

### 3. Enable Required Apache Modules
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```
*Purpose: Enables the Apache rewrite module which is required for Laravel's routing system and restarts Apache to apply changes.*

### 4. Configure Apache
```bash
sudo nano /etc/apache2/sites-available/laravel.conf
```

Add this configuration:
```apache
<VirtualHost *:80>
    ServerName your_domain_or_public_ip
    DocumentRoot /var/www/laravel/public

    <Directory /var/www/laravel/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```
*Purpose: Creates an Apache virtual host that serves your Laravel application and enables .htaccess rewrite functionality.*

### 5. Enable the Site
```bash
sudo a2ensite laravel.conf
sudo a2dissite 000-default.conf
sudo systemctl restart apache2
```
*Purpose: Enables your Laravel site configuration, disables the default site, and restarts Apache to apply changes.*

### 6. Deploy Laravel Application
```bash
# Create web directory
sudo mkdir -p /var/www/laravel

# Clone your repository
sudo git clone https://github.com/yourusername/your-repo.git /var/www/laravel
# OR copy your files
# sudo cp -r /path/to/your/project/* /var/www/laravel/

# Set permissions
sudo chown -R www-data:www-data /var/www/laravel
sudo chmod -R 755 /var/www/laravel
sudo chmod -R 775 /var/www/laravel/storage
sudo chmod -R 775 /var/www/laravel/bootstrap/cache
```
*Purpose: Creates the web directory, clones your Laravel application code, and sets proper ownership and permissions so the web server can read/write necessary files.*

### 7. Configure Laravel
```bash
cd /var/www/laravel

# Copy environment file
sudo cp .env.example .env
sudo nano .env
```

Update the .env with your RDS details:
```
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

```bash
# Install dependencies
sudo composer install --no-dev --optimize-autoloader

# Generate key
sudo php artisan key:generate

# Cache configuration
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```
*Purpose: Sets up Laravel's environment configuration, installs production dependencies, generates an application key, and caches configurations for better performance.*

### 8. Verify Installation
Visit your server's IP address or domain in your browser to confirm your Laravel application is running correctly.

## Troubleshooting Tips

### Permission Issues
If you encounter permission issues:
```bash
sudo chown -R www-data:www-data /var/www/laravel
sudo chmod -R 775 /var/www/laravel/storage
sudo chmod -R 775 /var/www/laravel/bootstrap/cache
```

### Database Connection Issues
1. Verify RDS security group allows connections from your EC2 instance
2. Check database credentials in .env file
3. Test direct connection to RDS:
```bash
mysql -h your-rds-endpoint.rds.amazonaws.com -u username -p
```

### Web Server Issues
Check the status of your web server:
```bash
# For Nginx
sudo systemctl status nginx

# For Apache
sudo systemctl status apache2
```

Check error logs:
```bash
# For Nginx
sudo tail -f /var/log/nginx/error.log

# For Apache
sudo tail -f /var/log/apache2/error.log

# For Laravel
sudo tail -f /var/www/laravel/storage/logs/laravel.log
```

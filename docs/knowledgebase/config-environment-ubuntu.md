# Configure environment on ubuntu

To install the Apache web server on Ubuntu, open your terminal and run the core command: sudo apt update && sudo apt install apache2 -y. This command updates your local package repositories and installs the Apache web server alongside all necessary software dependencies.Follow this comprehensive guide to install, configure, and secure your new Apache web server on Ubuntu.1. Install ApacheUpdate your package index and fetch the web server by running the standard installation commands:bashsudo apt update
sudo apt install apache2 -y
Use code with caution.2. Configure the FirewallIf you use the Uncomplicated Firewall (UFW), you must explicitly allow web traffic to pass through.Check the available application profiles:bashsudo ufw app list
Use code with caution.Allow traffic using the 'Apache Full' profile to open both port 80 (HTTP) and port 443 (HTTPS) simultaneously:bashsudo ufw allow 'Apache Full'
Use code with caution.3. Verify Server StatusUbuntu automatically enables and starts the server process right after a successful installation.Check the live process status:bashsudo systemctl status apache2
Use code with caution.Verify it returns active (running) in the text output.Navigate to http://your_server_ip or http://localhost inside a web browser. The default Ubuntu Apache landing page will load to confirm everything functions normally.4. Manage the Apache ProcessYou can manage how the background daemon runs using standard systemctl syntax:Stop service: sudo systemctl stop apache2Start service: sudo systemctl start apache2Restart service: sudo systemctl restart apache2Reload configs (no disconnects): sudo systemctl reload apache2Disable boot autostart: sudo systemctl disable apache25. Host Your Website (Virtual Hosts)To host custom web domains, avoid adding files directly into the default root directory /var/www/html. Instead, follow the standard virtual host setup recommended in the Ubuntu Apache Documentation:Create a dedicated web root folder for your domain:bashsudo mkdir -p /var/www/example.com
Use code with caution.Create a new site configuration file:bashsudo nano /etc/apache2/sites-available/example.com.conf
Use code with caution.Paste the following configuration block into the empty file:apache<VirtualHost *:80>
    ServerName example.com
    ServerAlias www.example.com
    DocumentRoot /var/www/example.com
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
Use code with caution.Enable your newly designed configuration file:bashsudo a2ensite example.com.conf
Use code with caution.Disable the factory default configurations:bashsudo a2dissite 000-default.conf
Use code with caution.Always check your syntax for structural mistakes before applying changes:bashsudo apache2ctl configtest
Use code with caution.Reload the system daemon to push your updates live:bashsudo systemctl reload apache2
Use code with caution.6. Secure Your Traffic with SSL (Optional)To safely encrypt web traffic, read the DigitalOcean Let's Encrypt Guide to automatically fetch and configure free SSL/TLS certificates via Certbot:bashsudo snap install --classic certbot
sudo ln -sf /snap/bin/certbot /usr/bin/certbot
sudo certbot --apache
Use code with caution.If you are planning a production rollout, let me know if you need help setting up database management (MySQL), installing PHP processing engines, or configuring custom DNS settings for your domain name.
#!/bin/bash
# Installs on Google Cloud Platform's Compute Engine for Ubuntu 18.04 LTS
# Make sure script is called with DNS name desired
if [ $# -ne 1 ]; then
    echo "Usage: sudo install.sh <DNS_Name>"
fi

# Name the service based on first part of DNS name
SITE=`echo $1 | sed -e 's/\..*//'`

# Install all required system packages
apt update
apt install -y python-pip python3-pip python3-dev build-essential libssl-dev libffi-dev python3-setuptools virtualenv nginx python-certbot-nginx libc6-dev-i386 g++-multilib zsh openssl upx bc

# Install python2 package for src_angr
pip install templite

# Install all required python packages
virtualenv -p python3 env
source env/bin/activate
pip install --upgrade -r requirements.txt

# Set up systemd service for site
sed s+PROJECT_USER+$SUDO_USER+ etc/systemd.template | sed s+PROJECT_DIR+$PWD+ > /etc/systemd/system/$SITE.service

# Configure nginx for site
sed s+PROJECT_HOST+$1+ etc/nginx.template | sed s+PROJECT_DIR+$PWD+ > /etc/nginx/sites-available/$SITE
ln -s /etc/nginx/sites-available/$SITE /etc/nginx/sites-enabled

# Change ownership to regular user, but make group www-data so that
#   nginx can access.  Note: unix socket must be created by nginx in
#   current directory so make www-data own the top level directory
chown -R $SUDO_USER $PWD
chgrp -R www-data $PWD
chmod g+wX -R $PWD
chown www-data $PWD

# For metactf, regular user creates solved directories that the server
#   must be able to write to.  Add user to www-data group.
usermod -g www-data $SUDO_USER
sed -i -e '/^UMASK/ s/022/002/' /etc/login.defs

# Restart all services
systemctl start $SITE
systemctl enable $SITE
systemctl restart nginx

certbot --nginx -d $1 -n -m wuchang@pdx.edu --agree-tos

#   must be able to write to.  Add user to www-data group.
echo "Installation complete.  Please logout and log back in for group and umask changes to take effect"

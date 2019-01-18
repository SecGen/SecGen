#!/bin/bash
if [[ "$1" != -f ]]; then
    echo "This will delete all docker images!"
    read -p "Continue? (y/n)"
    if [[ ! $REPLY =~ ^[Yy]$ ]]
    then
       echo exiting
       exit
    fi
fi
sudo systemctl stop docker
sudo rm -fr /var/lib/docker
sudo systemctl start docker
echo "All Docker images were removed."


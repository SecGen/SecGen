#!/bin/bash
: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
END
#
# Install Docker on a Fedora system, along with other packages required by Labtainers
#

# Current version of Fedora that works with labtainers
FEDORA_VERSION_WORK="25"

# Get the OS release
source /etc/os-release

if [ "$VERSION_ID" != "$FEDORA_VERSION_WORK" ]
then
    echo "Version $VERSION_ID of Fedora does not support Docker-ce, and therefore cannot be used for Labtainers."
    echo "The latest Fedora version that supports Docker-ce is $FEDORA_VERSION_WORK"
    exit 1
fi

#needed packages for install
#sudo dnf upgrade
sudo dnf -y install dnf-plugins-core

#sets up stable repository
sudo dnf config-manager --add-repo https://download.docker.com/linux/fedora/docker-ce.repo

#installs Docker: Community Edition
#sudo dnf upgrade
sudo dnf makecache fast
sudo dnf -y install docker-ce

#additional packages needed
sudo dnf -y install python-pip
sudo pip install --upgrade pip
sudo pip install netaddr parse python-dateutil
sudo dnf install -y openssh-server 
sudo dnf install -y xterm

#starts and enables docker
sudo systemctl start docker
sudo systemctl enable docker

#gives user docker commands
sudo groupadd docker
sudo usermod -aG docker $USER



#---Checking if packages have been installed. If not, the system will not reboot and allow the user to investigate.
declare -a packagelist=("dnf-plugins-core"  "docker-ce" "python-pip" "openssh-server")
packagefail="false"

for i in "${packagelist[@]}"
do
#echo $i
packagecheck=$(rpm -qa | grep $i)
#echo $packagecheck
    if [ -z "$packagecheck" ]; then
       if [ $i = docker-ce ];then 
           echo "ERROR: '$i' package did not install properly. Please check the terminal output above for any errors related to the pacakge installation. Run the install script two more times. If the issue persists, go to docker docs and follow the instructions for installing docker. (Make sure the instructions is CE and is for your Linux distribution,e.g., Ubuntu and Fedora.)"
       else
           echo "ERROR: '$i' package did not install properly. Please check the terminal output above for any errors related to the pacakge installation. Try installing the '$i' package individually by executing this in the command line: 'sudo apt-get install $i" 
       fi
       packagefail="true"
       #echo $packagefail
    fi
done

pipcheck=$(pip list 2> /dev/null | grep -F netaddr)
#echo $pipcheck
if [ -z "$pipcheck" ]; then
    echo "ERROR: 'netaddr' package did not install properly. Please check the terminal output for any errors related to the pacakge installation. Make sure 'python-pip' is installed and then try running this command: 'sudo -H pip install netaddr' "
    packagefail="true"
    #echo $packagefail
fi

pipcheck=$(pip list 2> /dev/null | grep -F parse)
#echo $pipcheck
if [ -z "$pipcheck" ]; then
    echo "ERROR: 'parse' package did not install properly. Please check the terminal output for any errors related to the package installation. Make sure 'python-pip' is installed and then try running this command: 'sudo -H pip install parse' "
    packagefail="true"
    #echo $packagefail
fi

if [ $packagefail = "true" ]; then
    exit 1
fi

exit 0

#Notes: The “-y” after each install means that the user doesn’t need to press “y” in between each package download. The install script is based on this page: https://docs.docker.com/engine/installation/linux/docker-ce/fedora/

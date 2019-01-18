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
# Install Docker on a CentOS system, along with other packages required by Labtainers
#

#needed packages for install
sudo yum makecache fast
sudo yum install -y yum-utils device-mapper-persistent-data lvm2

#sets up stable repository
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo

#installs Docker: Community Edition
sudo yum makecache fast
sudo yum install -y docker-ce

#additional packages needed
sudo yum --enablerepo=extras -y install epel-release
sudo yum install -y python-pip
sudo pip install --upgrade pip
sudo pip install netaddr parse python-dateutil
sudo yum install -y openssh-server 

#starts and enables docker
sudo systemctl start docker
sudo systemctl enable docker

#gives user docker commands
sudo groupadd docker
sudo usermod -aG docker $USER

#---Checking if packages have been installed. If not, the system will not reboot and allow the user to investigate.
declare -a packagelist=("yum-utils" "device-mapper-persistent-data" "lvm2" "epel-release" "docker-ce" "python2-pip" "openssh-server")
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

#Notes: The “-y” after each install means that the user doesn’t need to press “y” in between each package download. The install script is based on this page: https://docs.docker.com/engine/installation/linux/docker-ce/centos/

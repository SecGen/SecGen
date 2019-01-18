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
#Install Docker on a Debian system, along with other packages required by Labtainers
#
type sudo >/dev/null 2>&1 || { echo >&2 "Please install sudo.  Aborting."; exit 1; }
sudo -v || { echo >&2 "Please make sure user is sudoer.  Aborting."; exit 1; }
#needed packages for Docker install
sudo apt-get update
sudo apt-get -y install apt-transport-https ca-certificates curl gnupg2 software-properties-common 

#adds Docker’s official GPG Key
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo apt-key add -

#used to verify matching Key ID (optional)
#sudo apt-key fingerprint 0EBFCD88

#sets up stable repository
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/debian $(lsb_release -cs) stable"

#installs Docker:Community Edition
sudo apt-get update
sudo apt-get -y install docker-ce 

#gives user access to docker commands
sudo groupadd docker
sudo usermod -aG docker $USER

#enables and starts docker
sudo systemctl start docker
sudo systemctl enable docker

#additional packages needed for labtainers
sudo apt-get -y install python-pip 
sudo pip install --upgrade pip 
sudo pip install netaddr parse python-dateutil
sudo apt-get -y install openssh-server

#---Checking if packages have been installed. If not, the system will not reboot and allow the user to investigate.
declare -a packagelist=("apt-transport-https" "ca-certificates" "curl" "gnupg2" "software-properties-common"  "docker-ce" "python-pip" "openssh-server")
packagefail="false"

for i in "${packagelist[@]}"
do
#echo $i
packagecheck=$(dpkg -s $i 2> /dev/null | grep Status)
#echo $packagecheck
    if [ "$packagecheck" != "Status: install ok installed" ]; then
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

#Notes: The “-y” after each install means that the user doesn’t need to press “y” in between each package download. The install script is based on this page: https://docs.docker.com/engine/installation/linux/docker-ce/debian/

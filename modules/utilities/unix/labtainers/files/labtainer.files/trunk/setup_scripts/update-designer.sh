#!/bin/bash
#
# figure out where we are executing from and go to the labtainer directory
#
if [ "$#" -eq 1 ]; then
   if [ "$1" == "-t" ]; then
       export TEST_REGISTRY=TRUE
   else
       echo "update-labtainers [-t]"
       echo "   use -t to pull tar from /media/sf_SEED"
       echo "   and pull images from the test registry"
       exit
   fi
elif [ "$#" -ne 0 ]; then
   echo "update-labtainers [-t]"
   echo "   use -t to pull tar from /media/sf_SEED"
   echo "   and pull images from the test registry"
   exit
fi
here=`pwd`
if [[ $here == */labtainer ]]; then
   echo is at top >> /dev/null
elif [[ $here == */labtainer-student ]]; then
   #echo is in student
   real=`realpath ./`
   cd $real
   cd ../../..
elif [[ $here == */setup_scripts ]]; then
   cd ../../
else
   echo "Please run this script from the labtainer or labtainer-student directory"
   exit
fi
labtainer_root=`pwd`
target=~/.bashrc
grep ":scripts/designer/bin:" $target | grep PATH >>/dev/null
result=$?
if [[ result -ne 0 ]];then
   cat <<EOT >>$target
   if [[ ":\$PATH:" != *":scripts/designer/bin:"* ]]; then 
       export PATH="\${PATH}:$labtainer_root/trunk/scripts/designer/bin"
       export LABTAINER_DIR=$labtainer_root/trunk
   fi
EOT
fi
rm -f update-designer.sh
ln -s trunk/setup_scripts/update-designer.sh
full=`realpath trunk/setup_scripts/update-designer.sh`
ln -sf $full trunk/scripts/labtainer-student/bin/update-designer.sh
if [[ "$TEST_REGISTRY" != TRUE ]]; then
    wget https://my.nps.edu/documents/107523844/109121513/labtainer-developer.tar/f377285e-23b5-4cd4-a578-c879b0200fff -O labtainer-developer.tar
else
    cp /media/sf_SEED/labtainer-developer.tar .
    echo "USING SHARED FILE TAR, NOT PULLING FROM WEB"
fi
sudo trunk/setup_scripts/dns-add.py
sudo systemctl restart docker
sudo -H pip install netaddr parse python-dateutil
cd ..
# ad-hoc clean up.  remove after a while
rm -f labtainer/trunk/scripts/labtainer-student/bin/SimLab*

tar xf labtainer/labtainer-developer.tar
grep "^Distribution created:" labtainer/trunk/README.md | awk '{print "Updated to release of: ", $3, $4}'

if [ ! -L $HOME/Desktop/labdesigner.pdf ]; then
    read -p "Add desktop shortcut to Lab Designer Guide?(y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
       ln -s "$(pwd)"/labtainer/trunk/docs/labdesigner/labdesigner.pdf $HOME/Desktop/labdesigner.pdf
    fi
fi

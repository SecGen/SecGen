#!/bin/bash
#
#  Update a labtainers installation to use the latest tar and fetch the
#  latest baseline images
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
#
# figure out where we are executing from and go to the labtainer directory
#
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
rm -f update-labtainer.sh
ln -s trunk/setup_scripts/update-labtainer.sh
full=`realpath trunk/setup_scripts/update-labtainer.sh`
ln -sf $full trunk/scripts/labtainer-student/bin/update-labtainer.sh
test_flag=""
if [[ "$TEST_REGISTRY" != TRUE ]]; then
    wget https://my.nps.edu/documents/107523844/109121513/labtainer.tar/6fc80410-e87d-4e47-ae24-cbb60c7619fa -O labtainer.tar
    sync
else
    cp /media/sf_SEED/labtainer.tar .
    echo "USING SHARED FILE TAR, NOT PULLING FROM WEB"
    test_flag="-t -m"
fi
cd ..
tar xf labtainer/labtainer.tar --keep-newer-files --warning=none
cd labtainer/trunk/setup_scripts
./pull-all.py $test_flag
cd ../../..
#
# ensure labtainer paths in .bashrc
#
target=~/.bashrc
grep ":./bin:" $target >>/dev/null
result=$?
if [[ result -ne 0 ]];then
   cat <<EOT >>$target
   if [[ ":\$PATH:" != *":./bin:"* ]]; then 
       export PATH="\${PATH}:./bin"
   fi
EOT
fi
grep "^Distribution created:" labtainer/trunk/README.md | awk '{print "Updated to release of: ", $3, $4}'

#!/bin/bash
# Assume running from setup_scripts/
#
#  Runs as bash -c argument to gnome-terminal.  unable to get it to inherit bashrc defined env.
#
export TEST_REGISTRY=TRUE
export PATH="${PATH}:./bin:$HOME/labtainer/trunk/scripts/designer/bin:$HOME/labtainer/trunk/testsets/bin"

now=`date +"%s"`
exec > /media/sf_SEED/smokelogs/log-$now.log
exec 2>&1

#Clear out docker.
./destroy-docker.sh -f

# Update baseline and framework
./update-labtainer.sh -t

# Update test sets
./update-testsets.sh
cd ../scripts/labtainer-student
echo "start smoke test"
smoketest.py
sudo poweroff

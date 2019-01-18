#!/bin/bash
#
# Prepare a test system to use the testregistry for pulling
# labtainer images.
#
echo "10.20.200.41 testregistry" >> /etc/hosts
./testreg-add.py
systemctl restart docker


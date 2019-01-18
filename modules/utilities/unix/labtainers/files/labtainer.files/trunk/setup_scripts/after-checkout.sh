#!/bin/bash
#
# Intended to be run after a repo is checked out, e.g., via svn.
# These functions are otherwise done when creating a distribution. 
# 
#
./build-docs.sh
cd ../tool-src/capinout
./mkit.sh

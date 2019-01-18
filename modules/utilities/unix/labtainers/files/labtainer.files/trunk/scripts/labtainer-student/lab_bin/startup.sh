#!/usr/bin/env bash
: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
END
# startup.sh
# Arguments: None
#
# Usage: startup.sh
# 
# Description: Concatenate instructions.txt file and pipe to less
instructions="$HOME"/instructions.txt
if [ -f $instructions ]; then
   LOCKDIR=/tmp/.mylockdir
   if mkdir "$LOCKDIR" >/dev/null 2>&1; then
       echo "Starting startup.sh"
       cat $instructions | less
   fi
fi
if [ -f .local/bin/student_startup.sh ]; then
    source .local/bin/student_startup.sh
fi

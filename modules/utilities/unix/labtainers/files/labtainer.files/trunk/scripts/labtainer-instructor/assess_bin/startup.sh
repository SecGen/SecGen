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
# Description: run instructor.py to auto grading and display <labname>.grades.txt

grade_container="$HOME"/.local/.is_grade_container
LOCKDIR=/tmp/.mylockdir
if mkdir "$LOCKDIR" >/dev/null 2>&1; then
    if [ -f $grade_container ]; then
        $HOME/.local/bin/instructor.py
        cat "$HOME"/*.grades.txt | less
    fi
fi

#!/bin/bash
: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
#
# Add preexec hooks to the bash shell to capture stdin & stdout
#
END
MYHOME=$1
if [[ -f $MYHOME/.profile ]]; then
    target=$MYHOME/.profile
    root_target=/root/.profile
elif [[ -f $MYHOME/.bash_profile ]]; then
    target=$MYHOME/.bash_profile
    root_target=/root/.bash_profile
else
    echo "no profile, use .profile anyway?"
    target=$MYHOME/.profile
    root_target=/root/.bash_profile
fi
if grep --quiet startup.sh $target; then
    echo "already hooked" >>/dev/null
else
    #echo "hook not enabled, fix this"
    cat $MYHOME/.local/bin/profile-add >> $target
    echo "export DISPLAY=:0" >> $root_target
    cat $MYHOME/.local/bin/bashrc-add  |  sed 's@PRECMD_HOME_REPLACE_ME@'"$MYHOME"'@' >> $MYHOME/.bashrc
    cat $MYHOME/.local/bin/bashrc-add  |  sed 's@PRECMD_HOME_REPLACE_ME@'"$MYHOME"'@' >> /root/.bashrc
fi


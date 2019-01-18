#!/bin/bash
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
grep ":scripts/designer/bin:" $target | grep PATH >>/dev/null
result=$?
if [[ result -ne 0 ]];then
   here=`realpath ../`
   cat <<EOT >>$target
   if [[ ":\$PATH:" != *":scripts/designer/bin:"* ]]; then 
       export PATH="\${PATH}:$here/scripts/designer/bin"
       export LABTAINER_DIR=$here
   fi
EOT
fi

: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
END
local_output=""
getlocaloutput(){
   # find command line paramters that match requested treataslocal
   # token. or just return filename if that is what the token reflects.
   local in_param=$1
   #echo "in_param is $in_param"
   IFS=':' read -r -a array <<< "$in_param"
   #read -r -a array <<< "$in_param"
   if [[ ${#array[@]} == "2" ]];then
       delim_type=${array[0]}
       delim_value=${array[1]}
       index=0
       for field in "${cmd_line_array[@]}";do
           #echo "check field $field indix is $index"
           case $delim_type in
               starts)
                  if [[ $field == $delim_value* ]]; then
                      plen=${#delim_value}
                      local_output=${field:$plen}
                      return 0 
                  fi
                  ;;
               file)
                  local_output=$delim_value
                  return 0 
                  ;;
               follows)
                  if [[ $field == $delim_value ]]; then
                      index=$(expr $index + 1)
                      local_output=${cmd_line_array[$index]}
                      return 0 
                  fi
                  ;;
               *)
                  echo "unknown delimiter type $delim_type"
                  exit 1
           esac
           index=$(expr $index + 1)
       done 
   fi
}
treatlocal(){
   local cmd_path=$1
   local orig_command=$2
   #echo "cmd_path is $cmd_path"
   local TAS=$PRECMD_HOME/.local/bin/treataslocal
   if [ -f $TAS ]
   then
       local_output=""
       # Get the list of commands from treataslocal
       while read cmdlocal; do
           if [[ $cmdlocal == \#* ]]; then
               continue
           fi
           read -r -a cmd_array <<< "$cmdlocal"
           the_command=${cmd_array[0]}
           base_cmd=$(basename "$cmd_path")
           base_treat=$(basename "$the_command")

           if [[ $the_command == *.service ]]; then
               # special handling for service commands
               #echo special handling base_cmd $base_cmd
               if [[ $base_cmd == systemctl ]]; then
                   orig_cmd_array=($command)
                   action_index=1
                   if [ ${orig_cmd_array[0]} == "sudo" ]; then
                       action_index=2
                   fi
                   action=${orig_cmd_array[$action_index]}
                   if [[ $action == 'start' ]] || [[ $action == 'restart' ]]; then
                       service_index=`expr $action_index + 1`
                       service=${orig_cmd_array[$service_index]}
                       if [[ $base_treat == $service.service ]]; then
                           #echo will monitor $command
                           return 1
                       fi
                   fi
               elif [[ $base_cmd == service ]]; then
                   orig_cmd_array=($command)
                   action_index=2
                   if [ ${orig_cmd_array[0]} == "sudo" ]; then
                       action_index=3
                   fi
                   action=${orig_cmd_array[$action_index]}
                   if [[ $action == 'start' ]] || [[ $action == 'restart' ]]; then
                       service_index=`expr $action_index - 1`
                       service=${orig_cmd_array[$service_index]}
                       if [[ $base_treat == $service.service ]]; then
                           #echo will monitor $command
                           return 1
                       fi
                   fi
               elif [[ $cmd_path == /etc/init.d/* ]]; then
                   #echo "is init.d"
                   orig_cmd_array=($command)
                   action_index=1
                   if [ ${orig_cmd_array[0]} == "sudo" ]; then
                       action_index=2
                   fi
                   action=${orig_cmd_array[$action_index]}
                   #echo "action $action"
                   if [[ $action == 'start' ]] || [[ $action == 'restart' ]]; then
                       service=$base_cmd
                       if [[ $base_treat == $service.service ]]; then
                           #echo will monitor $command
                           return 1
                       fi
                   fi
               fi
           elif [[ "$base_cmd" == "$base_treat" ]]; then

               if [[ ${#cmd_array[@]} == "2" ]];then
                  the_param=${cmd_array[1]}
                  #echo "the_param is $the_param"
                  getlocaloutput $the_param 
                  #echo "set local to $local_output"
               fi
               return 1
           else
               continue
           fi
       done <$TAS
    fi
    return 0
}
ignorelocal(){
   local cmd_path=$1
   if [[ -z $cmd_path ]]; then
       return 1
   fi
   cmd=$(basename $cmd_path)
   if [[ "$cmd" == Student.py ]]; then
       return 1
   fi
   local TAS=$PRECMD_HOME/.local/bin/ignorelocal
   if [ -f $TAS ]
   then
       # Get the list of commands from ignorelocal
       while read cmdlocal; do
           if [[ "$cmd" == "$cmdlocal" ]]; then
               return 1
           else
               continue
           fi
       done <$TAS
    fi
    return 0
}
forcecheck(){
   local cmd_path=$1
   local TAS=$PRECMD_HOME/.local/bin/forcecheck
   if [ -f $TAS ]
   then
       # Get the list of commands from forcecheck
       while read cmdlocal; do
           if [[ "$cmd_path" == "$cmdlocal" ]]; then
               return 1
           else
               continue
           fi
       done <$TAS
    fi
    return 0
}
#
# Invoke the command in $1 using capinout,
# but only if it is not a system command.  Checks the
# ~/.local/bin/treataslocal for exceptions.
# If the command includes a pipe, look at both sides of the pipe.
# Ignore sudo, and treats target command as the command.
#
preexec() {
   #echo "just typed $1";
   history -a
   timestamp=$(date +"%Y%m%d%H%M%S")
   if [[ "$1" == "exit" ]]; then
       return 0
   fi
   amp="&"
   if [[ $1 == *"$amp"* ]]; then
       # do not track background processes
       return 0
   fi

   IFS='|' read -ra commandarray <<< "$1"
   #echo "command array: $commandarray"
   IFS=' '
   counter=0
   for command in "${commandarray[@]}";do
       #echo "loop for command $command"
       #
       # track whether target is left or right of pipe
       # TBD test for only one pipe
       # 
       if [[ $1 == *"|"* ]]; then
           counter=$[$counter +1]
       fi
       cmd_line_array=($command)
       if [ ${cmd_line_array[0]} == "sudo" ]; then
          cmd_path=`which ${cmd_line_array[1]} 2>/dev/null`
       else
          cmd_path=`which ${cmd_line_array[0]} 2>/dev/null`
       fi
       if [[ $cmd_path == alias* ]]; then
           IFS=$'\n' read -rd '' -a y <<<"$cmd_path"
           cmd_path=$(echo ${y[1]} | xargs)
       fi
       # do we want to run precheck on this command, though it is not otherwise tracked?
       forcecheck $cmd_path
       result=$?
       if [ $result == 1 ]; then
          if [ -f $PRECMD_HOME/.local/bin/precheck.sh ]
          then
              precheckoutfile="$PRECMD_HOME/.local/result/precheck.stdout.$timestamp"
              precheckinfile="$PRECMD_HOME/.local/result/precheck.stdin.$timestamp"
              $PRECMD_HOME/.local/bin/precheck.sh $cmd_path > $precheckoutfile 2>/dev/null
              if [[ ! -s $precheckoutfile ]]; then
                  rm -f $precheckoutfile
              fi
              # For now, there is nothing (i.e., no stdin) for precheck
              #echo "" >> $precheckinfile
          fi
          return 0
       fi
       # do we treat a system command as a local command to be tracked?
       treatlocal $cmd_path $command
       result=$?
       if [ $result == 1 ]; then
           #echo "will treat as local"
           # If file $PRECMD_HOME/.local/bin/precheck.sh exist, run it
           if [ -f $PRECMD_HOME/.local/bin/precheck.sh ]
           then
               precheckoutfile="$PRECMD_HOME/.local/result/precheck.stdout.$timestamp"
               precheckinfile="$PRECMD_HOME/.local/result/precheck.stdin.$timestamp"
               $PRECMD_HOME/.local/bin/precheck.sh $cmd_path > $precheckoutfile 2>/dev/null
               if [[ ! -s $precheckoutfile ]]; then
                   rm -f $precheckoutfile
               fi
               # For now, there is nothing (i.e., no stdin) for precheck
               #echo "" >> $precheckinfile
           fi
           /sbin/capinout "$1" $counter $timestamp $cmd_path
           if [[ ! -z "$local_output" ]]; then
               # we are to timestamp a program output file 
               #echo "local output is $local_output"
               just_command=$(basename "$cmd_path")
               cp $local_output $PRECMD_HOME/.local/result/$just_command.prgout.$timestamp
           fi
           return 1
       fi
       # do we ignore a non-system command?
       ignorelocal $cmd_path
       result=$?
       if [ $result == 1 ]; then
           return 0
       fi
       if [[ ! -z $cmd_path ]] && [[ "$cmd_path" != /usr/* ]] && \
          [[ "$cmd_path" != /bin/* ]] && [[ "$cmd_path" != /sbin/* ]] && \
          [[ "$cmd_path" != /etc/* ]]; then
           #echo "would do this command $1"
           # If file $PRECMD_HOME/.local/bin/precheck.sh exist, run it
           if [ -f $PRECMD_HOME/.local/bin/precheck.sh ]
           then
               precheckoutfile="$PRECMD_HOME/.local/result/precheck.stdout.$timestamp"
               precheckinfile="$PRECMD_HOME/.local/result/precheck.stdin.$timestamp"
               $PRECMD_HOME/.local/bin/precheck.sh $cmd_path > $precheckoutfile 2>/dev/null
               if [[ ! -s $precheckoutfile ]]; then
                   rm -f $precheckoutfile
               fi
               # For now, there is nothing (i.e., no stdin) for precheck
               #echo "" >> $precheckinfile
           fi
           /sbin/capinout "$1" $counter $timestamp $cmd_path
           return 1
       fi
   done
   return 0
}


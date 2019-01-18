#!/bin/bash
: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
END
# capinout.sh
# Description: * Re-direct stdin and stdout to files

# Usage: capinout.sh <execprog>
# Arguments:
#     <execprog> - program to execute

# trapfun - add program finish time
trapfun()
{
    #echo "PROGNAME is $PROGNAME"
    PGROUP=$(ps ax -o "%r %c" | grep $PROGNAME | awk '{print $1}')
    if [[ ! -z $PGROUP ]]; then
        #echo "PGROUP IS $PGROUP"
        HAS_ROOT=$(pgrep -u root -g $PGROUP)
        if [[ -z $HAS_ROOT ]]; then
            kill -TERM -$PGROUP
        else
            sudo kill -TERM -$PGROUP
        fi

        endtime=`date +"%Y%m%d%H%M%S"`
        echo "PROGRAM FINISH: $endtime" >> $stdinfile
        echo "PROGRAM FINISH: $endtime" >> $stdoutfile
    fi
}
tailingquote()
{
    local string=$1
    #if [[ "$string" == "*\'" ] || [ "$string" == "*\"" ]]; then
    if [[ "$string" == *\' ]]; then
        return 1
    fi
    if [[ "$string" == *\" ]]; then
        return 1
    fi
    return 0
}

pipe_sym="|"
redirect_sym=">"
append_sym=">>"
full=$1
counter=$2
timestamp=$3
cmd_path=$4
#echo "full is $full"
#
# Look for redirect, and remove from command
#
if [[ "$full" == *"$append_sym"* ]]; then
    IFS='>' read -ra COMMAND_ARRAY <<< "$full"
    tailingquote ${COMMAND_ARRAY[2]}
    result=$?
    if [ $result == 0 ]; then
        full=${COMMAND_ARRAY[0]}
        append_file=${COMMAND_ARRAY[2]}
        #echo "append file $append_file"
        IFS=' '
    fi
elif [[ "$full" == *"$redirect_sym"* ]]; then
    IFS='>' read -ra COMMAND_ARRAY <<< "$full"
    tailingquote ${COMMAND_ARRAY[2]}
    result=$?
    if [ $result == 0 ]; then
       full=${COMMAND_ARRAY[0]}
       redirect_file=${COMMAND_ARRAY[1]}
       IFS=' '
    fi
fi

if [[ "$full" == *"$pipe_sym"* ]]; then
    #echo is pipe has $pipe_sym
    IFS='|' read -ra COMMAND_ARRAY <<< "$full"
    if [ $counter == 2 ]; then
       # target on right of pipe
       prepostcommand=${COMMAND_ARRAY[0]}
       targetcommand=${COMMAND_ARRAY[1]}
    else
       prepostcommand=${COMMAND_ARRAY[1]}
       targetcommand=${COMMAND_ARRAY[0]}
    fi
    IFS=' '
    #echo "prepostcommand is $prepostcommand"
    #echo "target command is $targetcommand"
    TARGET_ARGS=($targetcommand)
    EXECPROG=${TARGET_ARGS[0]}
    if [ ${TARGET_ARGS[0]} == "sudo" ]; then
       PROGNAME=`basename ${TARGET_ARGS[1]}`
       PROGPATH=${TARGET_ARGS[1]}
    else
       PROGNAME=`basename ${TARGET_ARGS[0]}`
       PROGPATH=${TARGET_ARGS[0]}
    fi
    len=${#TARGET_ARGS[@]}
    if [ $len -gt 1 ]; then
       PROGRAM_ARGUMENTS=${TARGET_ARGS[@]:1:$len}
    else
       PROGRAM_ARGUMENTS=""
    fi
else
    targetcommand=${full}
    TARGET_ARGS=($targetcommand)
    EXECPROG=${TARGET_ARGS[0]}
    if [ ${TARGET_ARGS[0]} == "sudo" ]; then
       PROGNAME=`basename ${TARGET_ARGS[1]}`
       PROGPATH=${TARGET_ARGS[1]}
    else
       PROGNAME=`basename ${TARGET_ARGS[0]}`
       PROGPATH=${TARGET_ARGS[0]}
    fi
    len=${#TARGET_ARGS[@]}
    if [ $len -gt 1 ]; then
       PROGRAM_ARGUMENTS=${TARGET_ARGS[@]:1:$len}
    else
       PROGRAM_ARGUMENTS=""
    fi
fi

#echo "EXECPROG is ($EXECPROG)"
#echo "PROGNAME is ($PROGNAME)"
#echo "PROGRAM_ARGUMENTS is ($PROGRAM_ARGUMENTS)"
#echo "Program to execute is $EXECPROG"
#echo "PROGNAME is $PROGNAME"
if [[ $PROGNAME == systemctl ]]; then
    # special handling for service program stdin and stdout file names
    ARG_ARRAY=($PROGRAM_ARGUMENTS)
    if [[ $EXECPROG == sudo ]]; then
        stdinfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[2]}.service.stdin.$timestamp"
        stdoutfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[2]}.service.stdout.$timestamp"
    else
        stdinfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[1]}.service.stdin.$timestamp"
        stdoutfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[1]}.service.stdout.$timestamp"
    fi
elif [[ $PROGNAME == service ]]; then
    # special handling for service program stdin and stdout file names
    ARG_ARRAY=($PROGRAM_ARGUMENTS)
    if [[ $EXECPROG == sudo ]]; then
        stdinfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[1]}.service.stdin.$timestamp"
        stdoutfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[1]}.service.stdout.$timestamp"
    else
        stdinfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[0]}.service.stdin.$timestamp"
        stdoutfile="$PRECMD_HOME/.local/result/${ARG_ARRAY[0]}.service.stdout.$timestamp"
    fi
elif [[ $PROGPATH == /etc/init.d/* ]]; then
    # special handling for service program stdin and stdout file names
    ARG_ARRAY=($PROGRAM_ARGUMENTS)
    stdinfile="$PRECMD_HOME/.local/result/$PROGNAME.service.stdin.$timestamp"
    stdoutfile="$PRECMD_HOME/.local/result/$PROGNAME.service.stdout.$timestamp"
else
    stdinfile="$PRECMD_HOME/.local/result/$PROGNAME.stdin.$timestamp"
    stdoutfile="$PRECMD_HOME/.local/result/$PROGNAME.stdout.$timestamp"
fi
#echo stdout $stdoutfile

# Store programs arguments into stdinfile
echo "PROGRAM_ARGUMENTS is ($PROGRAM_ARGUMENTS)" >> $stdinfile

#echo "stdinfile is $stdinfile"
#echo "stdoutfile is $stdoutfile"

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

# kill the tee when the pipe consumer dies
#
#set -o pipefail

# Setup trap to handle SIGINT and SIGTERM
trap "echo exiting due to signal; echo caught SIGINT >> $stdinfile; trapfun " SIGINT
trap "echo exiting due to signal; echo caught SIGTERM >> $stdinfile; trapfun " SIGTERM

pipe=$(mktemp -u)
if ! mkfifo $pipe; then
   echo "ERROR: pipe create failed" >%2
   exit 1
fi

exec 3<>$pipe
rm $pipe
if [ -z "$prepostcommand" ]; then
#
# no pipe
#
   if [ -n "$redirect_file" ]; then
       (echo $BASHPID >&3; tee -a $stdinfile) | (eval funbuffer -p $EXECPROG $PROGRAM_ARGUMENTS; r=$?; kill $(head -n1 <&3); exit $r) | tee $stdoutfile > $redirect_file
   elif [ -n "$append_file" ]; then
       (echo $BASHPID >&3; tee -a $stdinfile) | (eval funbuffer -p $EXECPROG $PROGRAM_ARGUMENTS; r=$?; kill $(head -n1 <&3); exit $r) | tee $stdoutfile >> $append_file
   else
       (echo $BASHPID >&3; tee -a $stdinfile) | (eval funbuffer -p $EXECPROG $PROGRAM_ARGUMENTS; r=$?; kill $(head -n1 <&3); exit $r) | tee $stdoutfile
   fi
else
#
# no pipe
#
   if [ $counter == 2 ];then
#
#    target on right side of pipe
#
      if [ -n "$redirect_file" ]; then
   
          (echo $BASHPID >&3; eval $prepostcommand | tee -a $stdinfile) | (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r) | tee $stdoutfile > $redirect_file
   
      elif [ -n "$append_file" ]; then
   
          (echo $BASHPID >&3; eval $prepostcommand | tee -a $stdinfile) | (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r) | tee $stdoutfile >> $append_file
   
      else
          #echo "prepostcommand before is $prepostcommand"
   
          (echo $BASHPID >&3; eval $prepostcommand | tee -a $stdinfile) | (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r) | tee $stdoutfile
      fi
   else
#
#     target on left side of pipe
#
      if [ -n "$redirect_file" ]; then
   
          (echo $BASHPID >&3; (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r | tee -a $stdinfile)) | tee $stdoutfile | (eval $prepostcommand) > $redirect_file
   
      elif [ -n "$append_file" ]; then
   
          (echo $BASHPID >&3; (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r | tee -a $stdinfile)) | tee $stdoutfile | (eval $prepostcommand) >> $append_file
   
      else
          #echo "prepostcommand before is $prepostcommand"
          (echo $BASHPID >&3; (eval $EXECPROG $PROGRAM_ARGUMENTS; r=$?; exit $r | tee -a $stdinfile)) | tee $stdoutfile | (eval $prepostcommand)
      fi
   fi
fi


TEE_PID=$(ps | grep [t]ee | awk '{print $1}')
if [ ! -z "$TEE_PID" ]; then
    endtime=`date +"%Y%m%d%H%M%S"`
    echo "PROGRAM FINISH: $endtime" >> $stdinfile
    echo "PROGRAM FINISH: $endtime" >> $stdoutfile
    kill $TEE_PID
fi


#exit ${PIPESTATUS[1]}

###### Call
#####tee -a $stdinfile | stdbuf -oL -eL $EXECPROG $PROGRAM_ARGUMENTS | tee $stdoutfile


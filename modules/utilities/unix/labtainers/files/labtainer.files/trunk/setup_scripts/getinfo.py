#!/usr/bin/env python

# Filename: getinfo.py
# Description: simple script to get Linux host resources

import os
import subprocess
import sys

def getMemoryInGB():
    # Get the MemTotal portion from /proc/meminfo
    command="cat /proc/meminfo | grep MemTotal"
    #print "command is (%s)" % command
    result=subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    MemTotalString=result.stdout.read().strip().split()
    lenMemTotalString=len(MemTotalString)
    #print "MemTotal is (%s)" % MemTotalString
    #print "length MemTotal is (%d)" % lenMemTotalString

    MemSize=None
    MemSizeType=None
    # Note: format of MemTotal is "MemTotal: <number> kB"
    if lenMemTotalString != 3:
        print "Invalid memory total string"
        exit(1)
    else:
        MemSize=MemTotalString[1]
        MemSizeType=MemTotalString[2]
        #print "MemSize is (%s) in (%s)" % (MemSize, MemSizeType)

    MemSizeGB = None
    if MemSize != None and MemSizeType != None and MemSizeType == "kB":
        try:
            MemSizeMB = float(MemSize) / 1024
        except:
            print "Invalid memory size string"
            exit(1)
        MemSizeGB = MemSizeMB / 1024
        MemSizeGB = float("%.2f" % MemSizeGB)
        #print "MemSize is (%s) in MB" % MemSizeMB
        #print "MemSize is (%s) in GB" % MemSizeGB

    #if MemSizeGB < 1.5:
    #    print "less than 1.5"
    #elif MemSizeGB > 1.5 and MemSizeGB < 2.0:
    #    print "greater than 1.5 but less than 2.0"
    #else:
    #    print "greater than 2.0"
    return MemSizeGB


def getNumProcessor():
    # Count the number of processor(s) in /proc/cpuinfo
    command="cat /proc/cpuinfo | grep processor | wc -l"
    #print "command is (%s)" % command
    result=subprocess.Popen(command, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    NumProcessorString=result.stdout.read().strip()
    #print "NumProcessor is (%s)" % NumProcessor
    try:
        NumProcessor = int(NumProcessorString)
    except:
        print "Invalid number of processor string"
        exit(1)
    return NumProcessor

def main():
    numprocessor = getNumProcessor()
    memoryinGB = getMemoryInGB()
    print "Linux host resources:"
    print "Processors: %d" % numprocessor
    print "RAM: %.2f GB" % memoryinGB
    print ""
    if numprocessor == 1:
        print "Labtainers will perform better with two processors allocated to the Linux host."
    if memoryinGB < 1.8:
        print "Labtainers may perform better with at least 2 GB of RAM allocated to the Linux host."
    if numprocessor == 1 or memoryinGB < 1.8:
        user_input=None
        user_input=raw_input("Would like to shutdown the host so you can allocate more resources? (yes/no)\n")
        user_input=user_input.strip().lower()
        #print "user_input (%s)" % user_input
        if user_input == "yes":
            command="sudo shutdown -h now"
            #print "command is (%s)" % command
            os.system(command)

    return 0

if __name__ == '__main__':
    sys.exit(main())



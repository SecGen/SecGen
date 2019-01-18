#!/usr/bin/env python
'''
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
'''

# Filename: configtest.py
# Description:
# Validate a set of configuration files against expected results.
# This script will invoke validate.py script
#
#

import subprocess
import sys
import os
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import labutils
import logging
import LabtainerLogging

CONFIGTEST_ROOT = os.path.abspath('../../testsets/validate')

def usage():
    sys.stderr.write("Usage: configtest.py\n")
    sys.exit(1)

# Usage: configtest.py
# Arguments: None
def main():
    labnamelist = []
    num_args = len(sys.argv)
    if num_args == 1:
        labnamelist = os.listdir(CONFIGTEST_ROOT)
    else:
        usage()

    #labnamelist = []
    #labnamelist.append("results_check_00011")
    dir_path = os.path.dirname(os.path.realpath(__file__))
    dir_path = dir_path[:dir_path.index("bin")]	

    found_error = False
    for labname in sorted(labnamelist):
        labutils.logger = LabtainerLogging.LabtainerLogging("configtest.log", labname, "../../config/labtainer.config")
        labutils.logger.INFO("Begin logging configtest.py for %s lab" % labname)
        labutils.logger.DEBUG("Current test name is (%s)" % labname)
        fulllabname = os.path.join(CONFIGTEST_ROOT, labname)

        if os.path.isdir(fulllabname):
            labutils.logger.DEBUG("(%s) is directory - assume (%s) is a labname" % (fulllabname, labname))
    
            # ConfigTest will do following:
            # 1. Invoke validate.py against the lab - should create a labtainer.log file
            # 2. Get the last line from labtainer.log (lastline)
            # 3. Compare lastline against expected.log
            command = "validate.py -c %s > /dev/null" % labname
            #os.system(command)
            result = subprocess.call(command, shell=True, stderr=subprocess.PIPE)
            expectedlog = os.path.join(fulllabname, "expected.log")
            labtainerlog = os.path.join(dir_path, "labtainer.log")
            fh = open(expectedlog, 'r')
            for line in fh:
                pass
            expectedlogline = line.strip().split(']')
            fh.close()
            fh = open(labtainerlog, 'r')
            for line in fh:
                pass
            labtainerlogline = line.strip().split(']')
            fh.close()
            expected_string = expectedlogline[1]
            labtainer_string = labtainerlogline[1]
            labutils.logger.DEBUG("expected_string is (%s)" % expected_string)
            labutils.logger.DEBUG("labtainer_string is (%s)" % labtainer_string)

            if expected_string != labtainer_string:
                labutils.logger.ERROR("validate (%s) fails!" % labname)
                labutils.logger.ERROR("expected string (%s)" % expected_string.strip())
                labutils.logger.ERROR("got this string (%s) instead!" % labtainer_string.strip())
                found_error = True
                break


    if found_error:
        labutils.logger.ERROR("Validate test encountered an error!")
    else:
        # No error
        labutils.logger.DEBUG("NO ERROR found")
        print "NO ERROR found"
    return 0

if __name__ == '__main__':
    sys.exit(main())


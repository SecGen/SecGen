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

# Filename: stop.py
# Description:
# This is the stop script to be run by the instructor.
# Note:
# 1. It needs 'start.config' file, where
#    <labname> is given as a parameter to the script.
#

import getpass
import re
import subprocess
import zipfile

import sys
import os
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import labutils
import logging
import LabtainerLogging

# Usage: stop.py <labname>
# Arguments:
#    <labname> - the lab to stop
def main():
    if len(sys.argv) > 2:
        sys.stderr.write("Usage: stop.py [<labname>]\n")
        sys.exit(1)
    
    lablist = []
    if len(sys.argv) == 2:
        labname = sys.argv[1]
        labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
        lablist.append(labname)
    else:
        labname = "all"
        # labutils.logger need to be set before calling GetListRunningLab()
        labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
        lablist = labutils.GetListRunningLab()

    for labname in lablist:
        labutils.logger.INFO("Begin logging stop.py for %s lab" % labname)
        labutils.logger.DEBUG("Instructor CWD = (%s), Student CWD = (%s)" % (instructor_cwd, student_cwd))
        # Pass 'False' to ignore_stop_error (i.e., do not ignore error)
        lab_path = os.path.join(os.path.abspath('../../labs'), labname)
        has_running_containers, running_containers_list = labutils.GetRunningContainersList()
        if has_running_containers:
            has_lab_role, labnamelist = labutils.GetRunningLabNames(running_containers_list, "instructor")
            if has_lab_role:
                if labname not in labnamelist:
                    labutils.logger.ERROR("No lab named %s in currently running labs!" % labname)
                    sys.exit(1)
            else:
                labutils.logger.ERROR("No running labs in instructor's role")
                sys.exit(1)
        else:
            labutils.logger.ERROR("No running labs at all")
            sys.exit(1)
        labutils.StopLab(lab_path, "instructor", False)

    return 0

if __name__ == '__main__':
    sys.exit(main())


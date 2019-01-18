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

# Filename: watermarktest.py
# Description:
# Watermark Testing script. This script will make use of labutils.
#
#

import sys
import os
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import labutils
import logging
import LabtainerLogging

def usage():
    sys.stderr.write("Usage: watermarktest.py [<labname> | -a <labname>]\n")
    sys.stderr.write("       <labname> : watermark test on <labname> only\n")
    sys.stderr.write("       -a <labname> : continue running watermark test from <labname>\n")
    sys.exit(1)

# Usage: watermarktest.py
# Arguments: None
LABS_ROOT = os.path.abspath('../../labs')
def main():
    labnamelist = []
    num_args = len(sys.argv)
    choplist = False
    if num_args == 1:
        labnamelist = os.listdir(LABS_ROOT)
    elif num_args == 2:
        labnamelist.append(sys.argv[1])
    elif num_args == 3:
        dash_a = sys.argv[1]
        if dash_a != "-a":
            usage()
        labnamelist = os.listdir(LABS_ROOT)
        labnamestart = sys.argv[2]
        if labnamestart not in labnamelist:
            sys.stderr.write("Using non-existent <labname> with -a option!\n")
            usage()
        choplist = True
    else:
        usage()

    finallabnamelist = []
    if choplist:
        startfound = False
        for labname in sorted(labnamelist):
            if not startfound:
                if labname == labnamestart:
                    finallabnamelist.append(labname)
                    startfound = True
                else:
                    continue
            else:
                finallabnamelist.append(labname)
    else:
        finallabnamelist = labnamelist

    for labname in sorted(finallabnamelist):
        labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
        labutils.logger.INFO("Begin logging watermarktest.py for %s lab" % labname)
        labutils.logger.DEBUG("Current name is (%s)" % labname)
        fulllabname = os.path.join(LABS_ROOT, labname)
        if labname == "etc" or labname == "bin":
            labutils.logger.DEBUG("skipping etc or bin")
            continue

        if os.path.isdir(fulllabname):
            labutils.logger.DEBUG("(%s) is directory - assume (%s) is a labname" % (fulllabname, labname))
    
            # Watermark will do test following:
            # 1. This will stop containers of a lab, create or update lab images and start the containers.
            # 2. After the containers are started, it will invoke 'instructor.py' on the GRADE_CONTAINER.
            # 3. Stop the containers to obtain the 'grades.txt'
            # 4. Compare 'grades.txt.GOLD' vs. 'grades.txt'
	    dir_path = os.path.dirname(os.path.realpath(__file__))
	    dir_path = dir_path[:dir_path.index("trunk")] 
	    dir_path += "trunk/testsets/watermark/" + labname
            if not os.path.isdir(dir_path):
                labutils.logger.INFO("no tests found for "+labname)
                continue

	    crude_standards = os.listdir(dir_path)
	    standards = []
	    isFirstRun = True
	    for items in crude_standards:
		if "." not in items:
		    standards.append(items)
            lab_path = os.path.join(LABS_ROOT, labname)
	    for standard in standards:
            	WatermarkTestResult = labutils.WatermarkTest(lab_path, "instructor", standard, isFirstRun=isFirstRun)	
		isFirstRun = False
            	if WatermarkTestResult == False:
                # False means grades.txt.GOLD != grades.txt, output error then break
                    print("WatermarkTest fails on %s lab %s" % (labname, standard))
                    sys.exit(1)
            	else:
                    print("WatermarkTest on %s lab SUCCESS %s" % (labname, standard))

    return 0

if __name__ == '__main__':
    sys.exit(main())


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

# Filename: regresstest.py
# Description:
# Regression Testing script. This script will make use of labutils.
#
#

import sys
import os
import getpass
import time
import imp
gradelab = imp.load_source('gradelab', 'bin/gradelab')
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import labutils
import logging
import LabtainerLogging
import ParseLabtainerConfig

def usage():
    sys.stderr.write("Usage: regresstest.py [<labname> | -a <labname>]\n")
    sys.stderr.write("       <labname> : regression test on <labname> only\n")
    sys.stderr.write("       -a <labname> : continue running regression test from <labname>\n")
    sys.exit(1)

LABS_ROOT = os.path.abspath('../../labs')


def compareGrades(GradesGold, Grades):
    GradesGoldLines = {}
    GradesLines = {}
    with open(GradesGold) as gradesgoldfile:
        for line in gradesgoldfile:
            linestrip = line.strip()
            if not linestrip or linestrip.startswith("#"):
                continue
            linetoken = linestrip.split()
            student_email = linetoken[0]
            if "_at_" in student_email:
                if student_email in GradesGoldLines:
                    logger.ERROR("GradesGold file error: Multiple entries for the same student's e-mail %s" % student_email)
                    return False
                else:
                    new_line = line.strip().replace(" ", "")
                    GradesGoldLines[student_email] = new_line
    with open(Grades) as gradesfile:
        for line in gradesfile:
            linestrip = line.strip()
            if not linestrip or linestrip.startswith("#"):
                continue
            linetoken = linestrip.split()
            student_email = linetoken[0]
            if "_at_" in student_email:
                if student_email in GradesLines:
                    logger.ERROR("Grades file error: Multiple entries for the same student's e-mail %s" % student_email)
                    return False
                else:
                    new_line = line.strip().replace(" ", "")
                    GradesLines[student_email] = new_line
    if GradesGoldLines == GradesLines:
        return True
    else:
        return False


def RegressTest(lab_path, standard, logger):
    labname = os.path.basename(lab_path)
    labtainer_config_dir = os.path.join(os.path.dirname(os.path.dirname(lab_path)), 'config', 'labtainer.config')
    labtainer_config = ParseLabtainerConfig.ParseLabtainerConfig(labtainer_config_dir, logger)

    labutils.is_valid_lab(lab_path)
    regresstest_lab_path = os.path.join(labtainer_config.testsets_root, labname, standard)
    host_home_xfer = os.path.join(labtainer_config.host_home_xfer, labname)
    logger.DEBUG("Host Xfer directory for labname %s is %s" % (labname, host_home_xfer))
    logger.DEBUG("Regression Test path for labname %s is %s" % (labname, regresstest_lab_path))

    GradesGold = "%s/%s.grades.txt" % (regresstest_lab_path, labname)
    username = getpass.getuser()
    Grades = "/home/%s/%s/%s.grades.txt" % (username, host_home_xfer, labname)
    logger.DEBUG("GradesGold is %s - Grades is %s" % (GradesGold, Grades))

    is_regress_test = standard
    check_watermark = False
    auto_grade = True
    debug_grade = False
    gradelab.doGrade(labname, False, False, True, False, regress_test=GradesGold)

#    for name, container in start_config.containers.items():
#        mycontainer_name       = container.full_name
#        container_user         = container.user
#
#        if mycontainer_name == start_config.grade_container:
#            logger.DEBUG('about to RunInstructorCreateDradeFile for container %s' % start_config.grade_container)
#            RunInstructorCreateGradeFile(start_config.grade_container, container_user, labname, check_watermark)

    # Pass 'True' to ignore_stop_error (i.e., ignore stop error)

    CompareResult = False
    # GradesGold and Grades must exist
    logger.DEBUG('compare %s to %s' % (GradesGold, Grades))
    if not os.path.exists(GradesGold):
        logger.ERROR("GradesGold %s file does not exist!" % GradesGold)
    elif not os.path.exists(Grades):
        logger.ERROR("Grades %s file does not exist!" % Grades)
    else:
        CompareResult = compareGrades(GradesGold, Grades)
    return CompareResult

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
        labutils.logger = LabtainerLogging.LabtainerLogging("labtainer-regress.log", labname, "../../config/labtainer.config")
        labutils.logger.INFO("Begin logging regresstest.py for %s lab" % labname)
        labutils.logger.DEBUG("Current name is (%s)" % labname)
        fulllabname = os.path.join(LABS_ROOT, labname)
        if labname == "etc" or labname == "bin":
            labutils.logger.DEBUG("skipping etc or bin")
            continue

        if os.path.isdir(fulllabname):
            labutils.logger.DEBUG("(%s) is directory - assume (%s) is a labname" % (fulllabname, labname))
    
            # RegressTest will do test following:
	    dir_path = os.path.dirname(os.path.realpath(__file__))
	    dir_path = dir_path[:dir_path.index("scripts")] 
	    dir_path += "testsets/labs/" + labname
            if not os.path.isdir(dir_path):
                labutils.logger.INFO("no tests found for "+labname)
                continue

	    crude_standards = os.listdir(dir_path)
	    standards = []
	    for items in crude_standards:
		if "." not in items:
		    standards.append(items)
            lab_path = os.path.join(LABS_ROOT, labname)
            if len(standards) == 0:
                print('Did not find any subdirectories under %s' % lab_path)
                print('Test paths should be testsets/labs/[lab]/GOLD/...')
	    for standard in standards:
            	RegressTestResult = RegressTest(lab_path, standard, labutils.logger)	
            	if RegressTestResult == False:
                # False means grades.txt.GOLD != grades.txt, output error then break
                    print("RegressTest fails on %s lab %s" % (labname, standard))
                    sys.exit(1)
            	else:
                    print("RegressTest on %s lab SUCCESS %s" % (labname, standard))

    return 0

if __name__ == '__main__':
    sys.exit(main())


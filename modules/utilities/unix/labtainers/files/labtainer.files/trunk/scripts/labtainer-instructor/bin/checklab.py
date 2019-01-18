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

# Filename: checklab.py
# Description:
# To be run by the instructor to do sanity checks.
# Note:
# 1. It needs 'start.config' file, where
#    <labname> is given as a parameter to the script.
#

import getpass
import glob
import json
import md5
import os
import sys
import shutil
import stat

instructor_cwd = os.getcwd()
instructor_bin = os.path.join(instructor_cwd, 'assess_bin')
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
student_bin = os.path.join(student_cwd, 'lab_bin')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
sys.path.append(student_bin)
sys.path.append(instructor_bin)

import evalExpress
import labutils
import logging
import LabtainerLogging
import ParseStartConfig

def check_cmdinit(filename):
    cmdinit_found = False
    cmdinit_string = '["/usr/sbin/init"]'
    with open(filename, "r") as fh:
        filelines = fh.readlines()
    for line in filelines:
        if "CMD" in line:
            splitline = line.split()
            if (splitline[0] == "CMD" and splitline[1] == cmdinit_string):
                cmdinit_found = True
                break
    return cmdinit_found

def check_dockerfile_base(filename, basestring):
    base_found = False
    sourcebasestring = "mfthomps/%s" % basestring
    with open(filename, "r") as fh:
        filelines = fh.readlines()
    for line in filelines:
        if sourcebasestring in line:
            splitline = line.split()
            if (splitline[0] == "FROM" and splitline[1] == sourcebasestring):
                base_found = True
                break
    return base_found

def VerifyBashScriptExecutable(lab_path, labname, logger):
    bashfilepath = "%s/*/_bin/*.sh" % lab_path
    bashfiles = glob.glob(bashfilepath)
    for eachfile in bashfiles:
        f_stat = os.stat(eachfile)
        if not f_stat.st_mode & stat.S_IXUSR:
            logger.WARNING("File (%s) not executable!\n" % eachfile)
    

def VerifyDockerVsStartConfig(lab_path, labname, logger):
    config_path       = os.path.join(lab_path,"config") 
    start_config_path = os.path.join(config_path,"start.config")
    start_config = ParseStartConfig.ParseStartConfig(start_config_path, labname, "instructor", labutils.logger)

    dockerfilepath = "%s/dockerfiles/Dockerfile.%s.*" % (lab_path, labname)
    dockerfiles = glob.glob(dockerfilepath)
    # Verify each dockerfile separately
    for eachfile in dockerfiles:
         basefilename = os.path.basename(eachfile).split('.')
         mycontainername = basefilename[2]
         uses_labtainer_firefox = check_dockerfile_base(eachfile, "labtainer.firefox")
         uses_labtainer_java = check_dockerfile_base(eachfile, "labtainer.java")
         uses_labtainer_centos = check_dockerfile_base(eachfile, "labtainer.centos")
         uses_labtainer_lamp = check_dockerfile_base(eachfile, "labtainer.lamp")
         if (uses_labtainer_centos or uses_labtainer_lamp):
             for container_name, container in start_config.containers.items():
                 if mycontainername == container_name:
                     if not (container.script == "" or container.script == "none"):
                         logger.WARNING("Expecting SCRIPT NONE setting for labtainer.centos or labtainer.lamp!\n")

             has_cmd_init = check_cmdinit(eachfile)
             if not has_cmd_init:
                 logger.WARNING('Expecting (CMD ["/usr/sbin/init"]) setting for labtainer.centos or labtainer.lamp!\n')

         if (uses_labtainer_firefox or uses_labtainer_java):
             for container_name, container in start_config.containers.items():
                 if mycontainername == container_name:
                     if not (container.x11 == "yes"):
                         logger.WARNING("Expecting X11 YES setting for labtainer.firefox or labtainer.java!\n")


def VerifyHomeTar(lab_path, labname, logger):
    hometar = "%s/*/home_tar/home.tar" % lab_path
    logger.DEBUG("home tar (%s)" % hometar)
    hometarlist = glob.glob(hometar)
    logger.DEBUG("home tar list (%s)" % hometarlist)

    for eachhometar in hometarlist:
        pathsplit = eachhometar.split(lab_path)
        hometarsplit = pathsplit[1].split('/')
        container = hometarsplit[1]
        dockerfilestudent = "%s/dockerfiles/Dockerfile.%s.%s.student" % (lab_path, labname, container)
        if not (os.path.exists(dockerfilestudent) and os.path.isfile(dockerfilestudent)):
            sys.stderr.write("Dockerfile %s missing!\n" % dockerfilestudent)
            sys.exit(1)
        tarlinefound = False
        with open(dockerfilestudent, "r") as fh:
            filelines = fh.readlines()
        for line in filelines:
            if "home.tar" in line:
                linesplit = line.split()
                if (linesplit[0] == "ADD" and
                    linesplit[1] == "$labdir/$imagedir/home_tar/home.tar" and
                    linesplit[2] == "$HOME"):
                    tarlinefound = True
                    break
        if not tarlinefound:
            logger.WARNING("Expecting line (ADD $labdir/$imagedir/home_tar/home.tar $HOME) in Dockerfile!\n")
        
def VerifySysTar(lab_path, labname, logger):
    systar = "%s/*/sys_tar/sys.tar" % lab_path
    logger.DEBUG("sys tar (%s)" % systar)
    systarlist = glob.glob(systar)
    logger.DEBUG("sys tar list (%s)" % systarlist)

    for eachsystar in systarlist:
        pathsplit = eachsystar.split(lab_path)
        systarsplit = pathsplit[1].split('/')
        container = systarsplit[1]
        dockerfilestudent = "%s/dockerfiles/Dockerfile.%s.%s.student" % (lab_path, labname, container)
        if not (os.path.exists(dockerfilestudent) and os.path.isfile(dockerfilestudent)):
            sys.stderr.write("Dockerfile %s missing!\n" % dockerfilestudent)
            sys.exit(1)
        tarlinefound = False
        with open(dockerfilestudent, "r") as fh:
            filelines = fh.readlines()
        for line in filelines:
            if "sys.tar" in line:
                linesplit = line.split()
                if (linesplit[0] == "ADD" and
                    linesplit[1] == "$labdir/$imagedir/sys_tar/sys.tar" and
                    linesplit[2] == "/"):
                    tarlinefound = True
                    break
        if not tarlinefound:
            logger.WARNING("Expecting line (ADD $labdir/$imagedir/sys_tar/sys.tar /) in Dockerfile!\n")
        

def DoSaneChecks(lab_path, labname, logger):
    labutils.is_valid_lab(lab_path)

    VerifyHomeTar(lab_path, labname, logger)
    VerifySysTar(lab_path, labname, logger)
    VerifyDockerVsStartConfig(lab_path, labname, logger)
    VerifyBashScriptExecutable(lab_path, labname, logger)


# Usage: checklab.py <labname>
def main():
    num_args = len(sys.argv)
    if num_args != 2:
        sys.stderr.write("Usage: checklab.py <labname>\n")
        sys.exit(1)
    labname = sys.argv[1]

    labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
    labutils.logger.INFO("Begin logging checklab.py for %s lab" % labname)
    labutils.logger.DEBUG("Instructor CWD = (%s), Student CWD = (%s)" % (instructor_cwd, student_cwd))
    lab_path = os.path.join(os.path.abspath('../../labs'), labname)
    DoSaneChecks(lab_path, labname, labutils.logger)
    return 0

if __name__ == '__main__':
    sys.exit(main())

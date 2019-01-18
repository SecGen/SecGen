#!/usr/bin/env python
import os
import sys
import logging
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import labutils
import logging
import LabtainerLogging

labutils.logger = LabtainerLogging.LabtainerLogging("grader.log", 'grader', "../../config/labtainer.config")
has_running_containers, running_containers_list = labutils.GetRunningContainersList()
if has_running_containers:
    for container in running_containers_list:
        if container.endswith('-igrader'):
            cmd = 'docker stop %s' % container
            os.system(cmd)

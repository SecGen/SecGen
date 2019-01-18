#!/usr/bin/env python
'''
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public domain and is not subject to copyright. 
'''
import sys
import labutils
import logging
import LabtainerLogging
import os
import pydoc
import platform
import argparse
import stat
import subprocess
import CurrentLab
'''
Start a Labtainers exercise.
'''
def getLabVersion(path):
    if os.path.isfile(path):
        with open(path) as fh:
            line = fh.read().strip()
            lname, version = line.split()
        return lname, version
    return None, None

def getVerList(dirs, path):
    vlist = {}
    for lab in sorted(dirs):
        lpath = os.path.join(path, lab, 'config', 'version')
        lname, version = getLabVersion(lpath)
        if lname is not None: 
            if lname not in vlist:
                vlist[lname] = {}
            vlist[lname][lab] = int(version)
    return vlist

def hasLabInstalled(lab):
    cmd = 'docker ps -a | grep %s' % lab
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0]) > 0: 
        for line in output[0].splitlines():
            print line
            name = line.split()[1]
            thislab = os.path.basename(name.split('.')[0])
            if thislab == lab:
                return True
    return False

def isLatestVersion(versions, lab):
    if versions is not None:
        if lab in versions:
            this_version = versions[lab]
            for l in versions:
               if versions[l] > this_version:
                   if not hasLabInstalled(lab):
                       return False
    return True
        

def showLabs(dirs, path, versions, skip):
    description = ''
    description += 'Start a Labtainers lab\n'
    description+="List of available labs:\n\n"
    for loc in sorted(dirs):
        if loc in skip: 
            continue
    	versionfile = os.path.join(path, loc, "config", "version")
        lname, dumb = getLabVersion(versionfile)
        if lname is None or isLatestVersion(versions[lname], loc):
            description = description+'\n  '+loc
    	    aboutfile = os.path.join(path, loc, "config", "about.txt")
	
	    if(os.path.isfile(aboutfile)):
                description += ' - '
	        with open(aboutfile) as fh:
	            for line in fh:
                        description += line
            else:
                description += "\n"
                #sys.stderr.write(description)
    pydoc.pager(description)
    print('Use "-h" for help.')

def getRev():
    with open('../../README.md') as fh:
        for line in fh:
            if line.strip().startswith('Revision:'):
                parts = line.split(':')
                if len(parts) == 2 and len(parts[1].strip())>0:
                    return 'revision: '+parts[1].strip()
                else:
                    return "no revision, build environment"
    return '??'

def diagnose():
    xpath = '/tmp/.X11-unix/X0'
    if not os.path.exists(xpath):
        print("Missing %s, will prevent GUI's from running.  Try rebooting the Linux host" % xpath)
    else:
        print("No problems found with the environment.")
    
def main():
    dir_path = os.path.dirname(os.path.realpath(__file__))
    dir_path = dir_path[:dir_path.index("scripts/labtainer-student")]	
    path = dir_path + "labs/"
    dirs = os.listdir(path)
    rev = getRev()
    #revision='%(prog)s %s' % rev
    parser = argparse.ArgumentParser(prog='labtainer', description='Start a Labtainers lab.  Provide no arguments see a list of labs.')
    parser.add_argument('labname', default='NONE', nargs='?', action='store', help='The lab to run')
    parser.add_argument('-q', '--quiet', action='store_true', help='Do not prompt for email, use previoulsy supplied email.')
    parser.add_argument('-r', '--redo', action='store_true', help='Creates new instance of the lab, previous work will be lost.')
    parser.add_argument('-v', '--version', action='version', version='%(prog)s '+rev)
    parser.add_argument('-d', '--diagnose', action='store_true', help='Run diagnostics on the environment expected by Labtainers')
    parser.add_argument('-s', '--servers', action='store_true', help='Intended for distributed Labtainers, start the containers that are not clients.')
    parser.add_argument('-w', '--workstation', action='store_true', help='Intended for distributed Labtainers, start the client workstation.')
    parser.add_argument('-n', '--client_count', action='store', help='Number of clones of client components to create, itended for multi-user labs')
    parser.add_argument('-o', '--only_container', action='store', help='Run only the named container')
    parser.add_argument('-t', '--test_registry', action='store_true', default=False, help='Run with images from the test registry')
    num_args = len(sys.argv)
    versions = getVerList(dirs, path)
    if num_args < 2: 
        skip_labs = os.path.join(dir_path, 'distrib', 'skip-labs')
        skip = []
        if os.path.isfile(skip_labs):
            with open(skip_labs) as fh:
                for line in fh:
                    f = os.path.basename(line).strip()
                    skip.append(f)
        showLabs(dirs, path, versions, skip)
        exit(0)
    args = parser.parse_args()
    labname = args.labname
    if labname == 'NONE' and not args.diagnose:
        sys.stderr.write("Missing lab name\n" % labname)
        parser.usage()
        sys.exit(1)
    if args.diagnose:
        diagnose()
        if labname == 'NONE':
            exit(0)
    
    if labname not in dirs:
        sys.stderr.write("ERROR: Lab named %s was not found!\n" % labname)
        sys.exit(1)
    
    labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
    labutils.logger.INFO("Begin logging start.py for %s lab" % labname)
    lab_path = os.path.join(os.path.abspath('../../labs'), labname)
    update_flag='../../../.doupdate'
    if os.path.isfile(update_flag):
        ''' for prepackaged VMs, do not auto update after first lab is run '''
        os.remove(update_flag)
    #print('lab_path is %s' % lab_path)
    distributed = None
    if args.servers and args.workstation:
        print('--server and --workstation are mutually exclusive')
        exit(1)
    elif args.servers: 
        distributed = 'server' 
    elif args.workstation:
        distributed = 'client'
    if distributed is not None and args.client_count is not None:
        print('Cannot specify --server or --client if a --client_count is provided')
        exit(1)
    if args.test_registry:
        if os.getenv('TEST_REGISTRY') is None:
            #print('use putenv to set it')
            os.putenv("TEST_REGISTRY", "TRUE")
            ''' why does putenv not set the value? '''
            os.environ['TEST_REGISTRY'] = 'TRUE'
        else:
            #print('exists, set it true')
            os.environ['TEST_REGISTRY'] = 'TRUE'
        print('set TEST REG to %s' % os.getenv('TEST_REGISTRY'))

    if not args.redo:
        labutils.StartLab(lab_path, quiet_start=args.quiet, run_container=args.only_container, servers=distributed, clone_count=args.client_count)
    else:
        labutils.RedoLab(lab_path, quiet_start=args.quiet, 
                     run_container=args.only_container, servers=distributed, clone_count=args.client_count)
    current_lab = CurrentLab.CurrentLab()
    current_lab.add('lab_name', args.labname)
    current_lab.add('clone_count', args.client_count)
    current_lab.add('servers', distributed)
    current_lab.save()

    return 0

if __name__ == '__main__':
    sys.exit(main())


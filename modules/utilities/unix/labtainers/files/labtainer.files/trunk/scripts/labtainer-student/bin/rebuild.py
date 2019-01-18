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

# Filename: redo.py
# Description:
# For lab development testing workflow.  This will stop containers of a lab, create or update lab images
# and start the containers.
#

import sys
import os
import labutils
import logging
import LabtainerLogging
import argparse
import CurrentLab


# Usage: redo.py <labname> [-f]
# Arguments:
#    <labname> - the lab to stop, delete and start
#    [-f] will force a rebuild
#    [-q] will load the lab using a predetermined email.
def main():
    parser = argparse.ArgumentParser(description='Build the images of a lab and start the lab.')
    parser.add_argument('labname', help='The lab to build')
    parser.add_argument('-f', '--force', action='store_true', help='Force build of all containers in the lab.')
    parser.add_argument('-p', '--prompt', action='store_true', help='prompt for email, otherwise use stored')
    parser.add_argument('-C', '--force_container', action='store', help='force rebuild just this container')
    parser.add_argument('-o', '--only_container', action='store', help='run only this container')
    parser.add_argument('-t', '--test_registry', action='store_true', default=False, help='build from images in the test registry')
    parser.add_argument('-s', '--servers', action='store_true', help='Start containers that are not clients -- intended for distributed Labtainers')
    parser.add_argument('-w', '--workstation', action='store_true', help='Intended for distributed Labtainers, start the client workstation.')
    parser.add_argument('-n', '--client_count', action='store', help='Number of clones of client containers to create, intended for multi-user labs')
    parser.add_argument('-L', '--no_pull', action='store_true', default=False, help='Local building, do not pull from internet')

    args = parser.parse_args()
    quiet_start = True
    if args.prompt == True:
        quiet_start = False
    if args.force is not None:
        force_build = args.force
    #print('force %s quiet %s container %s' % (force_build, quiet_start, args.container))
    labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", args.labname, "../../config/labtainer.config")
    labutils.logger.INFO("Begin logging Rebuild.py for %s lab" % args.labname)
    lab_path = os.path.join(os.path.abspath('../../labs'), args.labname)

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

    distributed = None
    if args.servers and args.workstation:
        print('--server and --workstation are mutually exclusive')
        exit(1)
    elif args.servers: 
        distributed = 'server' 
    elif args.workstation:
        distributed = 'client'
    labutils.RebuildLab(lab_path, force_build=force_build, quiet_start=quiet_start, 
          just_container=args.force_container, run_container=args.only_container, servers=distributed, clone_count=args.client_count, no_pull=args.no_pull)
    current_lab = CurrentLab.CurrentLab()
    current_lab.add('lab_name', args.labname)
    current_lab.add('clone_count', args.client_count)
    current_lab.add('servers', distributed)
    current_lab.save()

    return 0

if __name__ == '__main__':
    sys.exit(main())


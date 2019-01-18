#!/usr/bin/env python
import os
import sys
import argparse
sys.path.append('../scripts/labtainer-student/bin')
import labutils
import ParseLabtainerConfig
import LabtainerLogging
import InspectLocalReg
import InspectRemoteReg
parser = argparse.ArgumentParser(description='Pull all base images if they do not yet exist')
parser.add_argument('-f', '--force', action='store_true', default=False, help='always pull latest')
parser.add_argument('-t', '--test_registry', action='store_true', default=False, help='pull all Labtainer base images')
parser.add_argument('-m', '--metasploit', action='store_true', default=False, help='include metasploitable and kali images')
args = parser.parse_args()

lab_config_file = os.path.join('../config', 'labtainer.config')
labutils.logger = LabtainerLogging.LabtainerLogging("pull.log", 'pull-all', "../config/labtainer.config")
logger = labutils.logger
labtainer_config = ParseLabtainerConfig.ParseLabtainerConfig(lab_config_file, logger)
test_registry = False
if not args.test_registry:
    env = os.getenv('TEST_REGISTRY')
    if env is not None and env.lower() == 'true':
        test_registry = True
if args.test_registry or test_registry:
    registry = labtainer_config.test_registry
else:
    registry = labtainer_config.default_registry
print('registry is: %s' % registry)
config_list = ['base', 'network', 'firefox', 'wireshark', 'java', 'centos', 'lamp']
if args.metasploit:
    config_list.append('metasploitable')
    config_list.append('kali')
for config in config_list:
    image_name = '%s/labtainer.%s' % (registry, config)
    local_created, local_user, local_version = labutils.inspectImage(image_name)
    if args.force or local_created is None:
        cmd = 'docker pull %s/labtainer.%s' % (registry, config)
        print(cmd)
        os.system(cmd)
    '''
    else:
        if test_registry:
            reg_created, reg_user, reg_version = InspectLocalReg.inspectLocal(image_name, registry)
        else:
            reg_created, reg_user, reg_version = InspectRemoteReg.inspectRemote(image_name)
    if local_created < reg_created:
        print(cmd)
        #os.system(cmd)
    '''
  



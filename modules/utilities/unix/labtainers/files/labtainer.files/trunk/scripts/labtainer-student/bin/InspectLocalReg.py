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
import os
import sys
import json
import subprocess
import VersionInfo
'''
Return creation date and user of a given image from a local registry, i.e.,
the test registry.
'''



def inspectLocal(image, test_registry, is_rebuild=False, quiet=False):
    use_tag = 'latest'
    digest = getDigest(image, 'latest', test_registry)
    if digest is None:
        return None, None, None, None, None
    created, user, version, base = getCreated(image, digest, test_registry)
    #print('base is %s' % base)
    if base is not None:
       base_image, base_id = base.rsplit('.', 1)
       my_id = VersionInfo.getImageId(base_image, quiet)
       if my_id == base_id:
           pass
           #print('got correct base_id')
       else:
            print('got WRONG base_id for base %s used in  %s my: %s  base: %s' % (base_image, image, my_id, base_id))
            tlist = getTags(image, test_registry)
            need_tag = 'base_image%s' % my_id
            if is_rebuild or need_tag in tlist:
                use_tag = need_tag
            elif quiet:
                cmd = 'docker pull %s' % base_image
                os.system(cmd)
            else:
                print('**************************************************')
                print('*  This lab will require a download of           *')
                print('*  several hundred megabytes.                    *')
                print('**************************************************')
                confirm = str(raw_input('Continue? (y/n)')).lower().strip()
                if confirm != 'y':
                    print('Exiting lab')
                    exit(0)
                else:
                    print('Please wait for download to complete...')
                    cmd = 'docker pull %s' % base_image
                    os.system(cmd)
                    print('Download has completed.  Wait for lab to start.')

    return created, user, version, use_tag, base


    
def getTags(image, test_registry):
    cmd =   'curl --silent --header "Accept: application/vnd.docker.distribution.manifest.v2+json"  "http://%s/v2/%s/tags/list"' % (test_registry, image)
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        j = json.loads(output[0])
        if 'tags' in j:
            return j['tags']
        else:
            return None
    else:
        return None

def getDigest(image, tag, test_registry):
    cmd =   'curl --silent --header "Accept: application/vnd.docker.distribution.manifest.v2+json"  "http://%s/v2/%s/manifests/%s"' % (test_registry, image, tag)
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        j = json.loads(output[0])
        if 'config' in j:
            return j['config']['digest']
        else:
            return None
    else:
        return None

def getCreated(image, digest, test_registry):
    cmd = 'curl --silent --location "http://%s/v2/%s/blobs/%s"' % (test_registry, image, digest)
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        j = json.loads(output[0])
        #print j['container_config']['User']
        version = None
        base = None
        if 'version' in j['container_config']['Labels']:
            version = j['container_config']['Labels']['version'] 
        if 'base' in j['container_config']['Labels']:
            base = j['container_config']['Labels']['base'] 
        return j['created'], j['container_config']['User'], version, base

#created, user, version, use_tag = inspectLocal('radius.radius.student', 'testregistry:5000', True)
#print '%s  user: %s version: %s use_tag %s' % (created, user, version, use_tag)

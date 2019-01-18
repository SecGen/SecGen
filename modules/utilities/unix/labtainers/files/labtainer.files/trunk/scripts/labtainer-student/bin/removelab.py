#!/usr/bin/env python
import subprocess
import sys
import os
import argparse

def removeLab(lab):
    cmd = 'docker ps -a'
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    lab_container = ' %s.' % lab
    container_list = []
    for line in output[0].splitlines():
        #print line
        if lab_container in line:
            container_list.append(line.split()[0]) 
    if len(container_list) > 0:
        cmd = 'docker rm %s' % ' '.join(container_list)
        print cmd
        os.system(cmd)
    
    
    cmd = 'docker images'
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    image_find = '/%s.' % lab
    image_find2 = '%s.' % lab
    image_list = []
    for line in output[0].splitlines():
        #print line
        if (image_find in line or line.startswith(image_find2)) and ' <none> ' not in line:
            parts = line.split()
            image = '%s:%s' % (parts[0], parts[1])
            image_list.append(image)
    if len(image_list) > 0:
        cmd = 'docker rmi -f %s' % ' '.join(image_list)
        print cmd
        os.system(cmd)
    else:
        print('No images for %s' % lab)

def main():
    parser = argparse.ArgumentParser(prog='removelab', description='Remove a lab and its images from a Labtainers installation. \
        The next time the lab is run, a fresh (updated) image will be pulled.')
    parser.add_argument('labname', default='NONE', nargs='?', action='store', help='The lab to delete')
    args = parser.parse_args()
    removeLab(args.labname)

if __name__ == '__main__':
    sys.exit(main())

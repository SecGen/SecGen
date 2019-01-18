#!/usr/bin/env python
import json
import os
import subprocess
'''
Update the docker/daemon.json file to reflect the test registry
'''
jfile = '/etc/docker/daemon.json'

if os.path.isfile(jfile):
    data = json.load(open(jfile))
else:
    #print('no file at %s' % jfile)
    if not os.path.isdir('/etc/docker'):
        os.path.mkdir('/etc/docker')
    data = {}

if 'insecure-registries' in data:
    print('already has insecure-registries %s' % data['insecure-registries'])
else:
    print('adding insecure-registries')
    data['insecure-registries'] = 'testregistry:5000'

    with open(jfile, 'w') as outfile:
        json.dump(data, outfile, indent = 4)
    

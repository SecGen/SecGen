#!/usr/bin/env python
import json
import os
import subprocess
'''
Update the docker/daemon.json file to reflect the local dns and the google dns.
Avoid trouble with sites that block use of external dns servers.
'''
jfile = '/etc/docker/daemon.json'
dns = []

cmd="nmcli dev show | grep 'IP4.DNS'"
ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
output = ps.communicate()
if len(output[0]) > 0:
    for line in output[0].splitlines(True):
        dns_add = line.split()[1].strip()
        dns.append(dns_add)
        break
dns.append("8.8.8.8")

if os.path.isfile(jfile):
    data = json.load(open(jfile))
else:
    print('no file at %s' % jfile)
    if not os.path.isdir('/etc/docker'):
        os.path.mkdir('/etc/docker')
    data = {}

if 'dns' in data:
    print('yes')
else:
    print('no')
    data['dns'] = dns

with open(jfile, 'w') as outfile:
    json.dump(data, outfile, indent = 4)
    

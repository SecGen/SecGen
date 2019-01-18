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
import time
import logging
import subprocess
from inotify_simple import INotify, flags

'''
This runs as a service on the containers. It uses inotify
to catch events defined in the .local/bin/notify file, 
and will invoke notify_cb.sh for when those events occur.
We pass the file, the mode, the the first user in the system to
notify_cb.sh   The timestamped output is appended to any
existing notify.stdout.... within 1 second of now.
Alternately, the notify file can include an optional output
filename.
 
It dies without a wimper.  Debug by manually running and generating
inotify events.
'''
logger = logging.getLogger('mynotify')
hdlr = logging.FileHandler('/tmp/mynotify.log')
formatter = logging.Formatter('%(asctime)s %(levelname)s %(message)s')
hdlr.setFormatter(formatter)
logger.addHandler(hdlr) 
logger.setLevel(logging.DEBUG)


class WatchType():
    def __init__(self, path, flag, outfile=None):
        self.path = path
        self.flag = flag
        self.outfile = outfile

def showMask(mask):
    if mask & flags.CREATE:
        print('CREATE')
    if mask & flags.ACCESS:
        print('ACCESS')
    if mask & flags.OPEN:
        print('OPEN')


def get_flag(flag):
    if flag == 'CREATE':
        return flags.CREATE
    elif flag == 'ACCESS':
        return flags.ACCESS
    elif flag == 'OPEN':
        return flags.OPEN
    else:
        return None

def get_first_user():
    with open('/etc/passwd') as fh:
        for line in fh:
            parts = line.strip().split(':')
            if parts[2] == '1000':
                return parts[0]
    return None

logger.debug('Start mynotify')
watches = {}

inotify = INotify()
first_user = get_first_user()
logger.debug('first user is %s' % first_user)
notify_file = '/home/%s/.local/bin/notify' % first_user
notify_cb = '/home/%s/.local/bin/notify_cb.sh' % first_user
results = '/home/%s/.local/result' % first_user

if not os.path.isfile(notify_file) and not os.path.isfile(notify_cb):
    logger.error('missing notify %s' % (notify_file))
    exit(0)

if not os.path.isfile(notify_cb):
    logger.debug("no notify_cb.sh, just ouput path & cmd")
    notify_cb = None

''' read in the notify file, set watches on file access as directed '''
with open(notify_file) as fh:
    for line in fh:
        if not line.strip().startswith('#'):
            parts = line.strip().split()
            outfile = None
            if len(parts) > 2:
                outfile = parts[2]
            watch = WatchType(parts[0], parts[1], outfile)
            flag = get_flag(watch.flag)
            try:
                wd = inotify.add_watch(watch.path, flag)
                watches[wd] = watch
            except:
                logger.debug('could not add watch for %s %s' % (watch.path, watch.flag))
#
# forever loop responding to inotify events
#
while True:
    for event in inotify.read():
        print(event)
        showMask(event.mask)
        watch = watches[event.wd]
        logger.debug('path: %s flag: %s' % (watch.path, watch.flag))
        now = time.time()
        ts = time.strftime('%Y%m%d%H%M%S', time.localtime(now))
        ''' use given outputfile name, if provided in the notify directive '''
        if watch.outfile is None:
            notifyoutfile = os.path.join(results, 'notify.stdout')
        else:
            notifyoutfile = os.path.join(results, '%s.stdout' % (watch.outfile))
        notifyoutfile_ts = '%s.%s' % (notifyoutfile, ts)
        #notifyoutfile = os.path.join(results, 'notify.stdin.%s' % ts)
        hist_file = '/home/%s/.bash_history' % first_user
        root_hist_file = '/root/.bash_history'
        if not (os.path.isfile(hist_file) or os.path.isfile(root_hist_file)):
            ''' no user yet, must be system startup, ignore '''
            continue
        cmd_time_history = os.path.getmtime(hist_file)
        root_hist_file = '/root/.bash_history'
        cmd_user = first_user
        if os.path.isfile(root_hist_file):
            time_root_history = os.path.getmtime(root_hist_file)
            if cmd_time_history > time_root_history:
                cmd_time_history = time_root_history
                hist_file = root_hist_file
                cmd_user = 'root'
        cmd = None
        with open(hist_file) as fh:
            hist = fh.readlines() 
            cmd = hist[-1].strip()
            if cmd.startswith('sudo'):
                cmd = cmd[5:]
                cmd_user = 'root'

        ''' determine if we should append to an existig output file '''
        is_a_file = False
        if not os.path.isfile(notifyoutfile_ts):
            ''' no file, if from previous second, use that as hack to merge with output from command '''
            now = now -1
            ts = time.strftime('%Y%m%d%H%M%S', time.localtime(now))
            tmpfile = '%s.%s' % (notifyoutfile, ts)
            if os.path.isfile(tmpfile):
                notifyoutfile_ts = tmpfile
                is_a_file = True
        else:
            is_a_file = True
          
        if is_a_file:    
            ''' existing file, append to it '''
            if notify_cb is not None:
                sys_cmd = '%s %s %s %s %s >> %s 2>/dev/null' % (notify_cb, watch.path, 
                          watch.flag, cmd_user, cmd, notifyoutfile_ts)
                os.system(sys_cmd)
                logger.debug('sys_cmd is %s' % sys_cmd)
            else:
                with open(notifyoutfile_ts, 'a') as fh:
                    fh.write('path: %s cmd: %s user: %s' % (watch.path, cmd, cmd_user))
        else:
            if notify_cb is not None:
                ''' only write to file if notify_cb generates output '''
                sys_cmd = '%s %s %s %s "%s"' % (notify_cb, watch.path, watch.flag, cmd_user, cmd)
                logger.debug('sys_cmd is %s' % sys_cmd)
                child = subprocess.Popen(sys_cmd, shell=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
                output = child.communicate()
                if len(output[0]) > 0:
                    with open(notifyoutfile_ts, 'w') as fh:
                        fh.write(output[0])
            else:
                with open(notifyoutfile_ts, 'a') as fh:
                    fh.write('path: %s cmd: %s user: %s' % (watch.path, cmd, cmd_user))

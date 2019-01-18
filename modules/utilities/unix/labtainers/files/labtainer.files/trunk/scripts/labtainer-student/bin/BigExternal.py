#!/usr/bin/env python
import sys
import os
import shutil
def BigExternal(lab_dir):
    '''
    Ensure large files named in the config/bigexternal.txt are present in the lab directory
    '''
    big_list = os.path.join(lab_dir,'config', 'bigexternal.txt')
    if not os.path.isfile(big_list):
        #print('Missing bigexternal.txt from %s' % big_list)
        return
    full = os.path.abspath(lab_dir)
    if os.path.isfile(big_list):
        with open(big_list) as fh:
            for line in fh:
               line = line.strip()
               if len(line) > 0 and not line.startswith('#'):
                   from_file, to_file = line.split()
                   to_path = os.path.join(lab_dir, to_file)
                   if not os.path.isfile(to_path):
                       print('missing %s, get it from %s' % (to_path, from_file))
                       exit(1) 
                   size = os.stat(to_path).st_size
                   if size < 50000:
                       print('File at %s is supposed to be large.' % to_path)
                       print('Get the correct %s from %s' % (to_path, from_file))
                       exit(1) 
                    
if __name__ == '__main__':               
    lab_dir = sys.argv[1]
    BigExternal(lab_dir)

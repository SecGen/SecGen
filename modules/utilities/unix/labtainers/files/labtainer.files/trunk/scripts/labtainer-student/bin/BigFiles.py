#!/usr/bin/env python
import sys
import os
import shutil
def BigFiles(lab_dir):
    '''
    Ensure large files named in the config/bigfiles.txt are present in the lab directory
    '''
    big_list = os.path.join(lab_dir,'config', 'bigfiles.txt')
    if not os.path.isfile(big_list):
        #print('Missing bigfiles.txt from %s' % big_list)
        return
    full = os.path.abspath(lab_dir)
    top = full[:full.index('labs')]
    if os.path.isfile(big_list):
        with open(big_list) as fh:
            for line in fh:
               line = line.strip()
               if len(line) > 0 and not line.startswith('#'):
                   from_file, to_file = line.split()
                   from_path = os.path.join(top,'bigfiles', from_file)
                   to_path = os.path.join(lab_dir, to_file)
                   if not os.path.isfile(to_path):
                       if not os.path.isfile(from_path):
                           print('Missing large file: %s' % from_path)
                           print('Get it from mynps.edu/cyberciege/downloads/%s' % from_file)
                           exit(1)
                       else:
                           shutil.copy2(from_path, to_path)
                    
if __name__ == '__main__':               
    lab_dir = sys.argv[1]
    BigFiles(lab_dir)

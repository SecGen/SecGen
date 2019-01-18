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

# MyUtil.py
# Description: * Various utilities

import re
import sys

# Strip beginning/ending whitespace from InputTag
# Return True if InputTag consists of alphanumeric, dash or underscore
# Otherwise, return False
def CheckAlphaDashUnder(InputTag):
    InputTagStripped = InputTag.strip()
    if re.match(r'^[a-zA-Z0-9_-]*$', InputTagStripped):
        # Stripped InputTag consists of alphanumeric, dash or underscore
        return True
    else:
        return False
  
def main():
    #print "Testing MyUtil.py"
    t_string = ' This string 123 has spaces in between '
    print('CheckAlphaDashUnder("%s") evaluates to %r' % (t_string, CheckAlphaDashUnder(t_string)))
    t_string = ' This-string-123-does_not-spaces-in-between '
    print('CheckAlphaDashUnder("%s") evaluates to %r' % (t_string, CheckAlphaDashUnder(t_string)))
    t_string = ' This-string-has-&-!-#-special-characters '
    print('CheckAlphaDashUnder("%s") evaluates to %r' % (t_string, CheckAlphaDashUnder(t_string)))

    return 0

if __name__ == '__main__':
    sys.exit(main())


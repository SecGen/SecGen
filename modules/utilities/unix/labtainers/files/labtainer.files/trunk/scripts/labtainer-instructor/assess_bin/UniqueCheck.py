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

# UniqueCheck.py
# Description: * Read unique.config
#              * Parse stdin and stdout files based on unique.config
#              * Create a json file

import json
import md5
import os
import re
import sys
import MyUtil
from parse import *

MYHOME = ""
logfilelist = []
line_types = ['CHECKSUM']
logger = None
uniqueidlist = {}

def findLineIndex(values):
    for ltype in line_types:
        if ltype in values:
            return values.index(ltype)

    return None

def ValidateUniqueConfig(actual_parsing, studentlabdir, container_list, labidname, each_key, each_value, logger):
    valid_field_types = ['CHECKSUM']
    if not MyUtil.CheckAlphaDashUnder(each_key):
        logger.ERROR("Not allowed characters in unique.config's key (%s)" % each_key)
        sys.exit(1)
    values = []
    # expecting:
    # . - [ filename ] : [<field_type>]
    #    field_type = (a valid_field_type defined above) - currently only CHECKSUM is supported

    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in each_value.split(' : ')]
    #print values
    numvalues = len(values)
    logger.DEBUG("each_value is %s -- numvalues is (%d)" % (each_value, numvalues))
    if numvalues < 2:
        logger.ERROR("found no ':' delimiter in %s" % each_value)
        sys.exit(1)
    if numvalues < 3 and values[1] not in line_types:
        logger.ERROR("Offending line: (%s).\n Perhaps there is a missing ':'?" % each_value)
        logger.ERROR("unique.config expected %s to be one of these: %s." % (values[1], str(line_types)))
        sys.exit(1)

    line_at = findLineIndex(values)
    if line_at is None:
        logger.ERROR('No line_type in %s' % each_value)
        sys.exit(1)
    num_splits = line_at+1
    #print "line_at is (%d) and num_splits is (%d)" % (line_at, num_splits)
     
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in each_value.split(' : ', num_splits)]

    newfilename = values[0].strip()
    logger.DEBUG('newfilename is %s' % newfilename)
    # <cfgcontainername>:<filename>
    if ':' in newfilename:
        cfgcontainername, filename = newfilename.split(':', 1)
    else:
        if len(container_list) > 1:
            logger.ERROR('No container name found in multi container lab entry (%s = %s)' % (each_key, each_value))
            sys.exit(1)
        cfgcontainername = ""
        filename = newfilename
    # Construct proper containername from cfgcontainername
    if cfgcontainername == "":
        containername = ""
    else:
        containername = labidname + "." + cfgcontainername + ".student"

    if filename not in logfilelist:
        logfilelist.append(filename)

    return newfilename

def handleUniqueConfig(labidname, line, nametags, studentlabdir, container_list, logger):
    retval = True
    targetlines = None
    #print('line is %s' % line)
    logger.DEBUG('line is %s' % line)
    (each_key, each_value) = line.split('=', 1)
    each_key = each_key.strip()

    #print each_key
    # Note: config file has been validated
    # Split into four parts or five parts
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in each_value.split(' : ')]
    line_at = findLineIndex(values)
    num_splits = line_at+1
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in each_value.split(' : ', num_splits)]
    newtargetfile = values[0].strip()
    logger.DEBUG('line_at is %d newtargetvalue = %s, values: %s' % (line_at, newtargetfile, str(values)))
    #print('newtargetfile is %s' % newtargetfile)
    # <cfgcontainername>:<exec_program>.<type>
    containername = None
    if ':' in newtargetfile:
        cfgcontainername, targetfile = newtargetfile.split(':', 1)
    else:
        ''' default to first container? '''
        #print('first cont is %s' % container_list[0])
        containername = container_list[0]
        targetfile = newtargetfile
    # Construct proper containername from cfgcontainername
    if containername is None:
        containername = labidname + "." + cfgcontainername + ".student"
    result_home = '%s/%s/%s' % (studentlabdir, containername, ".local/result/")

    if targetfile.startswith('/'):
        targetfile = os.path.join(result_home, targetfile[1:])
    #print('targetfile is %s containername is %s' % (targetfile, containername))
    logger.DEBUG('targetfile is %s, containername is %s' % (targetfile, containername))
    if containername is not None and containername not in container_list:
        print "Config line (%s) containername %s not in container list (%s), skipping..." % (line, containername, str(container_list))
        logger.DEBUG("Config line (%s) containername %s not in container list (%s), skipping..." % (line, 
              containername, str(container_list)))
        # set nametags - value pair to NONE
        nametags[targetfile] = "NONE"
        return False

    command = values[line_at].strip()
    targetfname_list = []

    if targetfile.startswith('~/'):
        targetfile = targetfile[2:]
    targetfname = os.path.join(studentlabdir, containername, targetfile)
    #print "targetfname is (%s)" % targetfname
    #print "labdir is (%s)" % studentlabdir

    targetfname_list.append(targetfname)

    #print "Current targetfname_list is %s" % targetfname_list

    tagstring = "NONE"
    # Loop through targetfname_list
    for current_targetfname in targetfname_list:
        if not os.path.exists(current_targetfname):
            # If file does not exist, treat as can't find token
            token = "NONE"
            logger.DEBUG("No %s file does not exist\n" % current_targetfname)
            nametags[targetfile] = token
            return False
        else:
            # Read in corresponding file
            targetf = open(current_targetfname, "r")
            targetlines = targetf.readlines()
            targetf.close()
            targetfilelen = len(targetlines)
            #print('current_targetfname %s' % current_targetfname)

            # command has been validated
            if command == 'CHECKSUM':
                ''' Create a checksum of the targetfile '''
                mymd5 = md5.new()
                targetlinestring = "".join(targetlines)
                mymd5.update(targetlinestring)
                tagstring = mymd5.hexdigest()
                nametags[targetfile] = tagstring
                #print('tag string is %s for eachkey %s' % (tagstring, each_key))
                return True
            else:
                # config file should have been validated
                # - if still unknown command, then should exit
                logger.ERROR('unknown command %s' % command)
                sys.exit(1)

def handleFileUniqueCheck(studentlabdir, labidname, configfilelines, outputjsonfname, container_list, logger):
    #print('in handleFileUniqueCheck outputjsonfile: %s' % outputjsonfname)
    nametags = {}
    got_one = False
    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip is not None and not linestrip.startswith('#') and len(line.strip())>0:
            got_one = got_one | handleUniqueConfig(labidname, linestrip, nametags, studentlabdir, container_list, logger)

    if got_one:
        #print nametags
        #print('will dump to %s' % outputjsonfname)
        jsonoutput = open(outputjsonfname, "w")
        for key in nametags:
            old = nametags[key]
            new = repr(old)
            nametags[key] = new
            #print('nametags[%s] = %s' % (key, new))
        try:
            jsondumpsoutput = json.dumps(nametags, indent=4)
        except:
            print('json dumps failed on %s' % nametags)
            exit(1)
        #print('dumping %s' % str(jsondumpsoutput))
        jsonoutput.write(jsondumpsoutput)
        jsonoutput.write('\n')
        jsonoutput.close()

# Note this can be called directly also
def ParseUniqueConfig(actual_parsing, homedir, studentlabdir, container_list, labidname, logger_in):
    MYHOME = homedir
    logger = logger_in
    configfilename = os.path.join(MYHOME,'.local','instr_config', 'unique.config')
    configfile = open(configfilename)
    configfilelines = configfile.readlines()
    configfile.close()

    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip:
            if not linestrip.startswith('#'):
                #print "Current linestrip is (%s)" % linestrip
                try:
                    (each_key, each_value) = linestrip.split('=', 1)
                except:
                     logger.ERROR('missing "=" character in %s' % linestrip)
                     sys.exit(1)
                each_key = each_key.strip()
                newfilename = ValidateUniqueConfig(actual_parsing, studentlabdir, container_list, labidname, each_key, each_value, logger)
                if each_key not in uniqueidlist:
                    uniqueidlist[each_key] = newfilename
        #else:
        #    print "Skipping empty linestrip is (%s)" % linestrip

    return configfilelines, uniqueidlist

def UniqueCheck(homedir, studentlabdir, container_list, instructordir, labidname, logger_in):
    MYHOME = homedir
    logger = logger_in
    actual_parsing = True
    # Parse and Validate unique.config file
    configfilelines, uniquelist = ParseUniqueConfig(actual_parsing, homedir, studentlabdir, container_list, labidname, logger_in)

    jsonoutputfilename = labidname
    logger.DEBUG("UniqueCheck: jsonoutputfilename is (%s) studentlabdir %s" % (jsonoutputfilename, studentlabdir))
  
    del logfilelist[:]

    #print "exec_proglist is: "
    #print exec_proglist
    #print "logfilelist is: "
    #print logfilelist
    OUTPUTRESULTHOME = '%s/%s' % (studentlabdir, ".local/result/")
    logger.DEBUG('Done with validate, outputresult to %s' % OUTPUTRESULTHOME)

    if not os.path.exists(OUTPUTRESULTHOME):
        os.makedirs(OUTPUTRESULTHOME)

    outputjsonfname = '%s%s.unique' % (OUTPUTRESULTHOME, jsonoutputfilename)
    handleFileUniqueCheck(studentlabdir, labidname, configfilelines, outputjsonfname, container_list, logger)

    uniquejsonfile = open(outputjsonfname, "r")
    uniquevalues = json.load(uniquejsonfile)
    uniquejsonfile.close()

    return uniquevalues



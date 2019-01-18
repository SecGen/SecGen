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

# ResultParser.py
# Description: * Read results.config
#              * Parse stdin and stdout files based on results.config
#              * Create a json file

import datetime
import json
import glob
import md5
import os
import re
import sys
import time
import MyUtil
import GoalsParser
import ParameterParser
from parse import *

MYHOME = ""
container_exec_proglist = {}
stdoutfnameslist = []
timestamplist = {}
line_types = ['CHECKSUM', 'CONTAINS', 'FILE_REGEX', 'FILE_REGEX_TS', 'LINE', 'STARTSWITH', 'NEXT_STARTSWITH', 'HAVESTRING', 
              'HAVESTRING_TS', 'LOG_TS', 'LOG_RANGE', 'REGEX', 'REGEX_TS', 'LINE_COUNT', 'PARAM', 'STRING_COUNT', 'COMMAND_COUNT', 'TIME_DELIM']
just_field_type = ['CHECKSUM', 'LINE_COUNT', 'TIME_DELIM']
logger = None
resultidlist = {}

def GetExecProgramList(containername, studentlabdir, container_list, targetfile):
    # This will return a list of executable program name matching
    # <directory>/.local/result/<exec_program>.targetfile.*
    # If containername is "" then loop through all directory of studentlabdir/container
    # where container is from the container_list
    # If containername is non "" then directory is studentlabdir/containername
    myexec_proglist = []
    mylist = []
    if containername == "":
        #print "containername is empty - do for all container in the container list"
        mylist = container_list
    else:
        #print "containername is non empty - do for that container only"
        mylist.append(containername)
    #print "Final container list is "
    #print mylist
    for cur_container in mylist:
        string_to_glob = "%s/%s/.local/result/*.%s.*" % (studentlabdir, cur_container, targetfile)
        #print "string_to_glob is (%s)" % string_to_glob
        globnames = glob.glob('%s' % string_to_glob)
        for name in globnames:
            basefilename = os.path.basename(name)
            #print "basefilename is %s" % basefilename
            split_string = ".%s" % targetfile
            #print "split_string is %s" % split_string
            namesplit = basefilename.split(split_string)
            #print namesplit
            if namesplit[0] not in myexec_proglist:
                myexec_proglist.append(namesplit[0])
    return myexec_proglist


def ValidateTokenId(result_value, token_id, logger):
    if token_id != 'ALL' and token_id != 'LAST':
        try:
            int(token_id)
        except ValueError:
            logger.ERROR("results.config line (%s)\n" % result_value)
            logger.ERROR("results.config has invalid token_id")
            sys.exit(1)

def findLineIndex(values):
    for ltype in line_types:
        if ltype in values:
            return values.index(ltype)

    return None

def ProcessConfigLine(actual_parsing, studentlabdir, container_list, labidname, result_key, result_value, logger):
    '''
    This function populates a set of global structures used in processing the results
    '''
    valid_field_types = ['TOKEN', 'GROUP', 'PARENS', 'QUOTES', 'SLASH', 'LINE_COUNT', 'CHECKSUM', 'CONTAINS','FILE_REGEX',  
                         'FILE_REGEX_TS', 'SEARCH', 'PARAM', 'STRING_COUNT', 'COMMAND_COUNT']
    if not MyUtil.CheckAlphaDashUnder(result_key):
        logger.ERROR("Not allowed characters in results.config's key (%s)" % result_key)
        sys.exit(1)
    values = []
    # See the Labtainer Lab Designer User guide for syntax

    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in result_value.split(' : ')]
    #print values
    numvalues = len(values)
    logger.DEBUG("result_value is %s -- numvalues is (%d)" % (result_value, numvalues))
    if numvalues < 2:
        logger.ERROR("found no ':' delimiter in %s" % result_value)
        sys.exit(1)
    if numvalues < 3 and values[1] not in just_field_type:
        logger.ERROR("Offending line: (%s).\n Perhaps there is a missing ':'?" % result_value)
        logger.ERROR("results.config expected %s to be one of these: %s." % (values[1], str(just_field_type)))
        sys.exit(1)

    line_at = findLineIndex(values)
    if line_at is None:
        logger.ERROR('No line_type in %s' % result_value)
        sys.exit(1)
    num_splits = line_at+1
    #print "line_at is (%d) and num_splits is (%d)" % (line_at, num_splits)
     
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in result_value.split(' : ', num_splits)]

    # get optional container name and determine if it is 'stdin' or 'stdout'
    newprogname_type = values[0].strip()
    logger.DEBUG('newprogname_type is %s' % newprogname_type)
    cmd = values[1].strip()
    # <cfgcontainername>:<exec_program>.<type>
    if ':' in newprogname_type:
        '''
        [container_name:]<prog>.[stdin | stdout] | [container_name:]file_path[:time_program]

        '''
        cfgcontainername = ''
        parts = newprogname_type.split(':')
        if len(parts) == 2:
            if parts[0].startswith('/'):
                progname_type =  parts[0]
                if len(container_list) > 1:
                    logger.ERROR('No container name found in multi container lab entry (%s = %s)' % (result_key, result_value))
                    sys.exit(1)
            else:
                cfgcontainername = parts[0]
                progname_type = parts[1]
        elif len(parts) == 3:
            cfgcontainername = parts[0]
            progname_type = parts[1]
    else:
        if len(container_list) > 1:
            logger.ERROR('No container name found in multi container lab entry (%s = %s)' % (result_key, result_value))
            sys.exit(1)
        if newprogname_type.endswith('stdin') or newprogname_type.endswith('stdout') \
             or newprogname_type.endswith('prgout'):
            cfgcontainername = container_list[0].split('.')[1]
            #print('assigned to %s' % cfgcontainername)
        else:
            cfgcontainername = ""
        progname_type = newprogname_type
    # Construct proper containername from cfgcontainername
    if cfgcontainername == "":
        containername = ""
    else:
        containername = labidname + "." + cfgcontainername + ".student"


    logger.DEBUG('Start to populate exec_program_list, progname_type is %s' % progname_type)
    # No longer restricted to stdin/stdout filenames 
    if ('stdin' not in progname_type) and ('stdout' not in progname_type) and ('prgout' not in progname_type):
        # Not stdin/stdout - add the full name
        logger.DEBUG('Not a STDIN or STDOUT: %s ' % progname_type)
    else:
        (exec_program, targetfile) = progname_type.rsplit('.', 1)
        exec_program_list = []
        # Can only parse for wildcard if it is actual parsing - not validation
        if exec_program == "*" and actual_parsing:
            exec_program_list = GetExecProgramList(containername, studentlabdir, container_list, targetfile)
            logger.DEBUG("wildcard, exec_program_list is %s" % exec_program_list)
        else:
            exec_program_list.append(exec_program)
            logger.DEBUG('exec_program %s, append to list, container is %s' % (exec_program, containername))
        if containername != "":
            #print('containername is %s' % containername)
            if containername not in container_exec_proglist:
                container_exec_proglist[containername] = []
            for cur_exec_program in exec_program_list:
                if cur_exec_program not in container_exec_proglist[containername]:
                    container_exec_proglist[containername].append(cur_exec_program)
            logger.DEBUG('proglist is %s' %  str(container_exec_proglist[containername]))
        else:
            if "CURRENT" not in container_exec_proglist:
                container_exec_proglist["CURRENT"] = []
            for cur_exec_program in exec_program_list:
                if cur_exec_program not in container_exec_proglist["CURRENT"]:
                    container_exec_proglist["CURRENT"].append(cur_exec_program)
            #print container_exec_proglist["CURRENT"]

        #print container_exec_proglist

    # Validate <field_type> - if exists (i.e., line_at == 3)
    #                       - because <field_type> is optional
    field_type = None
    if line_at == 3:
        field_type = values[1].strip()
        if field_type not in valid_field_types:
            logger.ERROR("results.config line (%s)\n" % result_value)
            logger.ERROR("results.config invalid field_type")
            sys.exit(1)

    # Sanity check for 'PARAM' type
    if values[line_at] == 'PARAM':
        logger.DEBUG("progname_type is (%s)" % progname_type)
        if not progname_type.endswith('stdin'):
            logger.ERROR("results.config line (%s)\n" % result_value)
            logger.ERROR("PARAM field_type on non stdin file")
            sys.exit(1)
        paramtoken_id = values[2].strip()
        try:
            paramindex = int(paramtoken_id) 
        except:
            logger.ERROR("results.config line (%s)\n" % result_value)
            logger.ERROR('PARAM field_type could not parse int from %s' % paramtoken_id)
            sys.exit(1)

    # If line_type1 (line_at != 1) - verify token id
    if field_type != 'SEARCH' and line_at != 1:
        token_index = 1
        if line_at == 3:
            token_index = 2
        ValidateTokenId(result_value, values[token_index], logger)

    if values[line_at] == 'LINE':
        try:
            int(values[line_at+1])
        except:
            logger.ERROR('Expected integer following LINE type, got %s in %s' % (values[line_at+1], result_value))
            sys.exit(1)

    return newprogname_type, cmd

def getToken(linerequested, field_type, token_id, logger):
        #print "Line requested is (%s)" % linerequested
        if linerequested == None:
            token = ''
        else:
            linetokens = {}
            if field_type == 'PARENS':
                myre = re.findall('\(.+?\)', linerequested)
                linetokenidx = 0
                for item in myre:
                    #print "linetokenidx = %d" % linetokenidx
                    linetokens[linetokenidx] = item[1:-1]
                    linetokenidx = linetokenidx + 1
                numlinetokens = len(linetokens)
            elif field_type == 'QUOTES':
                myre = re.findall('".+?"', linerequested)
                linetokenidx = 0
                for item in myre:
                    #print "linetokenidx = %d" % linetokenidx
                    linetokens[linetokenidx] = item[1:-1]
                    linetokenidx = linetokenidx + 1
                numlinetokens = len(linetokens)
            elif field_type == 'SLASH':
                myre = linerequested.split('/')
                linetokenidx = 0
                for item in myre:
                    #print "linetokenidx = %d" % linetokenidx
                    linetokens[linetokenidx] = item
                    linetokenidx = linetokenidx + 1
                numlinetokens = len(linetokens)
            elif field_type == 'SEARCH':
                logger.DEBUG('is search')
                search_results = search(token_id, linerequested)
                if search_results is not None:
                    token = str(search_results[0])
                else: 
                    token = None
            else:
                # field_type == "TOKEN"
                linetokens = linerequested.split()
                numlinetokens = len(linetokens)


            if token_id == 'ALL':
                token = linerequested.strip()
            elif token_id == 'LAST':
                if numlinetokens > 0:
                    token = linetokens[numlinetokens-1]
                else:
                    token = None
            elif field_type != 'SEARCH':
                #print linetokens
                # make sure tokenno <= numlinetokens
                tokenno = int(token_id)
                #print "tokenno = %d" % tokenno
                if tokenno > numlinetokens:
                    token = ""
                    #print "setting result to none tokenno > numlinetokens"
                else:
                    token = linetokens[tokenno-1]
        return token

def lineHasCommand(line, look_for):
    retval = 0
    if not look_for.startswith('time ') and line.startswith('time'):
        line = line[5:].strip()
    if not look_for.startswith('sudo ') and line.startswith('sudo'):
        line = line[5:].strip()
    commands = line.split(';')
    for c in commands:
        c = c.strip()
        pipes = c.split('|')
        for p in pipes:
            p = p.strip()
            if p.startswith('('):
                p = p[1:]
            if p.startswith(look_for):
                retval += 1
    return retval

def getTS(line):
    retval = None
    ''' try syslog format first '''
    ts_string = line[:15]
    now = datetime.datetime.now()
    ts_string = '%d %s' % (now.year, ts_string)
    try:
        time_val = datetime.datetime.strptime(ts_string, '%Y %b %d %H:%M:%S')
        retval = time_val.strftime("%Y%m%d%H%M%S")
    except:
        pass
    if retval is None:
        ''' snort format '''
        ts_string = line[:14]
        ts_string = '%d %s' % (now.year, ts_string)
        try:
            time_val = datetime.datetime.strptime(ts_string, '%Y %m/%d-%H:%M:%S')
            retval = time_val.strftime("%Y%m%d%H%M%S")
        except:
            pass
    
    if retval is None:
        ''' httpd format '''
        if '[' in line and ']' in line:
            ts_string = line[line.find("[")+1:line.find("]")].split()[0]
            try:
                time_val = datetime.datetime.strptime(ts_string, '%d/%b/%Y:%H:%M:%S')
                retval = time_val.strftime("%Y%m%d%H%M%S")
            except:
                pass
    if retval is None:
        print('ERROR getting timestamp from %s' % line)
    return retval 
         

def getTokenFromFile(current_targetfname, command, field_type, token_id, logger, lookupstring, line, result_key):
            # Read in corresponding file
            targetf = open(current_targetfname, "r")
            targetlines = targetf.readlines()
            targetf.close()
            targetfilelen = len(targetlines)
            #print('current_targetfname %s' % current_targetfname)

            # command has been validated to be either 'LINE' or 'STARTSWITH' or 'HAVESTRING'
            linerequested = None
            if command == 'LINE':
                # make sure lineno <= targetfilelen
                if lineno <= targetfilelen:
                    linerequested = targetlines[lineno-1]
            elif command == 'HAVESTRING':
                # command = 'HAVESTRING':
                found_lookupstring = False
                for currentline in targetlines:
                    if found_lookupstring == False:
                        if lookupstring in currentline:
                            found_lookupstring = True
                            linerequested = currentline
                            break
                # If not found - set to None
                if found_lookupstring == False:
                    linerequested = None
            elif command == 'REGEX':
                found_lookupstring = False
                for currentline in targetlines:
                    if found_lookupstring == False:
                        sobj = re.search(lookupstring, currentline)
                        if sobj is not None:
                            found_lookupstring = True
                            if field_type == 'GROUP':
                                linerequested = sobj
                            else:
                                linerequested = currentline
                            break
                # If not found - set to None
                if found_lookupstring == False:
                    linerequested = None
            elif command == 'HAVESTRING_TS' or command == 'LOG_RANGE' or \
                 command == 'REGEX_TS' or command == 'FILE_REGEX_TS' or command == 'LOG_TS':
                return None
            elif command == 'LINE_COUNT':
                return targetfilelen
            elif command == 'PARAM':
                fname = os.path.basename(current_targetfname).rsplit('.',1)[0] 
                if fname.endswith('stdin'):
                    program_name = current_targetfname.rsplit('.', 1)[0]
                else:
                    # Config file 'PARAM' has been validated against stdin
                    # Treat this as can't find token
                    logger.DEBUG('PARAM only valid for stdin files: %s' % current_targetfname)
                    return ''
                if 'PROGRAM_ARGUMENTS' in targetlines[0]:
                    try:
                        index = int(token_id) 
                    except:
                        # Config file 'PARAM' has been validated, should not be failing here
                        # If it does, then should really exit
                        logger.ERROR('could not parse int from %s' % token_id)
                        sys.exit(1)
                    if index == 0:
                        tagstring = program_name
                    else:
                        s = targetlines[0]
                        param_str = s[s.find("(")+1:s.find(")")]
                        params = param_str.split()
                        try:
                            tagstring = params[index-1]
                        except:
                            # Couldn't find the corresponding parameter
                            # Treat this as can't find token
                            logger.DEBUG('did not find parameter %d in %s' % (index-1, param_str))
                            return ''
                    return tagstring
            elif command == 'CHECKSUM':
                ''' Create a checksum of the targetfile '''
                mymd5 = md5.new()
                targetlinestring = "".join(targetlines)
                mymd5.update(targetlinestring)
                tagstring = mymd5.hexdigest()
                return tagstring
            elif command == 'CONTAINS':
                if token_id == 'CONTAINS':
                    ''' search entire file, vice searching for line '''
                    remain = line.split(command,1)[1]
                    remain = remain.split(':', 1)[1].strip()
                    tagstring = False
                    for currentline in targetlines:
                        #print('look for <%s> in %s' % (remain, currentline))
                        if remain in currentline:
                            tagstring = True
                            break 
                    return tagstring

            elif command == 'FILE_REGEX':
                    ''' search entire file, for line with given regex '''
                    remain = line.split(command,1)[1]
                    remain = remain.split(':', 1)[1].strip()
                    tagstring = False
                    allofit = ''.join(targetlines)
                    #print('%s' % allofit)
                    #print('look for %s' % remain)
                    sobj = re.findall(remain, allofit, re.MULTILINE | re.DOTALL)
                    if sobj is not None and len(sobj)>0:
                        tagstring = True
                    return tagstring


            elif command == 'STRING_COUNT':
                ''' search entire file, vice searching for line '''
                remain = line.split(command,1)[1]
                remain = remain.split(':', 1)[1].strip()
                count=0
                for currentline in targetlines:
                    #print('look for <%s> in %s' % (remain, currentline))
                    if remain in currentline:
                        count += 1
                return count

            elif command == 'COMMAND_COUNT':
                ''' intended for bash_history files, look for occurances of what look like commands '''
                remain = line.split(command,1)[1]
                look_for = remain.split(':', 1)[1].strip()
                count=0
                for currentline in targetlines:
                    occurances = lineHasCommand(currentline.strip(), look_for)
                    count += occurances
                return count

            elif command == 'STARTSWITH':
                #print('is startswith')
                found_lookupstring = False
                for currentline in targetlines:
                    if found_lookupstring == False:
                        if currentline.startswith(lookupstring):
                            found_lookupstring = True
                            linerequested = currentline
                            #print('line requested is %s' % linerequested)
                            break
                # If not found - set to None
                if found_lookupstring == False:
                    logger.DEBUG('*** No line starts with %s ***' % (lookupstring))
                    linerequested = None
            elif command == 'NEXT_STARTSWITH':
                found_lookupstring = False
                prev_line = None
                for currentline in targetlines:
                    if found_lookupstring == False:
                        if currentline.startswith(lookupstring) and prev_line is not None:
                            found_lookupstring = True
                            linerequested = prev_line
                            break
                    prev_line = currentline
                # If not found - set to None
                if found_lookupstring == False:
                    logger.DEBUG('*** No next line starts with %s ***' % (lookupstring))
                    linerequested = None
            else:
                # config file should have been validated
                # - if still unknown command, then should exit
                logger.ERROR('unknown command %s' % command)
                sys.exit(1)
            if command == 'REGEX' and field_type == 'GROUP':
                try:
                    token = linerequested.group(int(token_id))
                except:
                    token = ''
            else:
                token = getToken(linerequested, field_type, token_id, logger)
            logger.DEBUG('field_type %s, token_id %s, got token %s' % (field_type, token_id, token))
            return token
    
def getParamList(MYHOME, studentdir, logger):    
    lab_instance_seed = GoalsParser.GetLabInstanceSeed(studentdir, logger)
    container_user = ""
    param_filename = os.path.join(MYHOME, '.local', 'config',
          'parameter.config')

    pp = ParameterParser.ParameterParser(None, container_user, lab_instance_seed, logger)

    parameter_list = pp.ParseParameterConfig(param_filename)
    return parameter_list

def getConfigItems(labidname, line, studentlabdir, container_list, logger, parameter_list):
    targetlines = None
    if '=' not in line:
         print('no equals in %s' % line)
         return
    containername= targetfile= result_key= command= field_type= token_id= lookupstring= result_home  = None
    #print('line is %s' % line)
    logger.DEBUG('line is %s' % line)
    (result_key, result_value) = line.split('=', 1)
    result_key = result_key.strip()

    #print result_key
    # Note: config file has been validated
    # Split into four parts or five parts
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in result_value.split(' : ')]
    line_at = findLineIndex(values)
    num_splits = line_at+1
    # NOTE: Split using ' : ' - i.e., "space colon space"
    values = [x.strip() for x in result_value.split(' : ', num_splits)]
    newtargetfile = values[0].strip()
    logger.DEBUG('line_at is %d newtargetvalue = %s, values: %s' % (line_at, newtargetfile, str(values)))
    #print('newtargetfile is %s' % newtargetfile)
    # <cfgcontainername>:<exec_program>.<type>
    containername = None
    if ':' in newtargetfile:
        cfgcontainername, targetfile = newtargetfile.split(':', 1)
        #print('split got targetfile of %s' % targetfile)
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
        return None, None, result_key, None, None, None, None, None 

    command = values[line_at].strip()
    # field_type - if exists (because field_type is optional)
    #              has been validated to be one of the valid field types.
    #              
    # if it does not exists, default field_type is TOKEN
    if line_at == 3:
        field_type = values[1].strip()
    else:
        field_type = "TOKEN"
    # command has been validated to be either 'LINE' or 'STARTSWITH' or 'HAVESTRING'
    token_index = 1
    if line_at == 3:
        token_index = 2
    if command == 'PARAM':
        token_id = values[2].strip()
    elif command == 'CHECKSUM':
        token_id = None
    else:
        token_id = values[token_index].strip()
    if command == 'LINE':
        lineno = int(values[line_at+1].strip())
    elif command not in just_field_type:
        lookupstring = values[line_at+1].strip()
        if lookupstring.startswith('$'):
            tstring = lookupstring[1:]
            if tstring in parameter_list:
                logger.DEBUG('replacing %s with %s' % (lookupstring, parameter_list[tstring]))
                lookupstring = parameter_list[tstring]

    return containername, targetfile, result_key, command, field_type, token_id, lookupstring, result_home 


def handleConfigFileLine(labidname, line, nametags, studentlabdir, container_list, timestamppart, logger, parameter_list):
    retval = True
    containername, targetfile, result_key, command, field_type, token_id, lookupstring, result_home = getConfigItems(labidname, line, studentlabdir, container_list, logger, parameter_list)
    if targetfile is None:
        nametags[result_key]=None
        logger.ERROR('No target file in %s' % line)
        return retval
    logger.DEBUG('command %s, field_type %s, token_id %s' % (command, field_type, token_id))
    targetfname_list = []
    if targetfile.startswith('*'):
        # Handle 'asterisk' -- 
        #print "Handling asterisk"
        #print "containername is %s, targetfile is %s" % (containername, targetfile)
        # Replace targetfile as a list of files
        targetfileparts = targetfile.split('.')
        targetfilestdinstdout = None
        if targetfileparts is not None:
            targetfilestdinstdout = targetfileparts[1]
        if targetfilestdinstdout is not None:
            #print "targetfilestdinstdout is %s" % targetfilestdinstdout
            if containername in container_exec_proglist:
                myproglist = container_exec_proglist[containername]
            else:
                myproglist = container_exec_proglist["CURRENT"]
            for progname in myproglist:
                if timestamppart is not None:
                    targetfname = '%s%s.%s.%s' % (result_home, progname, targetfilestdinstdout, timestamppart)
                    targetfname_list.append(targetfname)
    else:
        #print "Handling non-asterisk"

        if timestamppart is not None:
            if not targetfile.startswith(result_home):
                targetfname = '%s%s.%s' % (result_home, targetfile, timestamppart)
            else:
                targetfname = '%s.%s' % (targetfile, timestamppart)
        else:
            ''' descrete file, no timestamp. '''
            if targetfile.startswith('~/'):
                targetfile = targetfile[2:]
            targetfname = os.path.join(studentlabdir, containername, targetfile)
        #print "labdir is (%s)" % studentlabdir

        targetfname_list.append(targetfname)

    #print "Current targetfname_list is %s" % targetfname_list

    tagstring = ""
    # Loop through targetfname_list
    # Will ONLY contain one entry, except for the case where astrix is used
    for current_targetfname in targetfname_list:
        if not os.path.exists(current_targetfname):
            # If file does not exist, treat as can't find token
            token = ""
            logger.DEBUG("No %s file does not exist\n" % current_targetfname)
            #nametags[result_key] = token
            return False
        else:
            token = getTokenFromFile(current_targetfname, command, field_type, token_id, logger, lookupstring, line, result_key)
            if token is None:
                return False

        #print token
        if token == "":
            tagstring = ""
        elif token is None:
            tagstring = None
        else:
            tagstring = token
            # found the token - break out of the main for loop
            break

    # set nametags - value pair
    if tagstring is not None:
        nametags[result_key] = tagstring
        return True
    else:
        return False


def ParseConfigForTimeRec(studentlabdir, labidname, configfilelines, ts_jsonfname, container_list, logger, parameter_list):
    ts_nametags = {}
    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip is not None and not linestrip.startswith('#') and len(line.strip())>0:
            containername, targetfile, result_key, command, field_type, token_id, lookupstring, result_home = getConfigItems(labidname, linestrip, studentlabdir, container_list, logger, parameter_list)
        
            if command == 'HAVESTRING_TS':
                if not os.path.isfile(targetfile):
                    continue
                with open(targetfile, "r") as fh:
                    targetlines = fh.readlines()
                for currentline in targetlines:
                    if lookupstring in currentline:
                        time_val = getTS(currentline)
                        if time_val is None:
                            continue
                        ts = str(time_val)
                        if ts not in ts_nametags:
                            ts_nametags[ts] = {}
                            ts_nametags[ts]['PROGRAM_ENDTIME'] = 0
                        token = getToken(currentline, field_type, token_id, logger)
                        ts_nametags[ts][result_key] = token

            if command == 'LOG_TS':
                if not os.path.isfile(targetfile):
                    continue
                with open(targetfile, "r") as fh:
                    targetlines = fh.readlines()
                for currentline in targetlines:
                    if lookupstring in currentline:
                        time_val = getTS(currentline)
                        if time_val is None:
                            continue
                        ts = str(time_val)
                        if ts not in ts_nametags:
                            ts_nametags[ts] = {}
                            ts_nametags[ts]['PROGRAM_ENDTIME'] = 0
                        ts_nametags[ts][result_key] = True

            if command == 'LOG_RANGE':
                if not os.path.isfile(targetfile):
                    continue
                with open(targetfile, "r") as fh:
                    targetlines = fh.readlines()
                prev_time_val = None
                last_time_val = None
                for currentline in targetlines:
                    time_val = getTS(currentline)
                    if time_val is None:
                        continue
                    if prev_time_val is None:
                        prev_time_val = time_val
                    if lookupstring in currentline:
                        last_time_val = time_val
                        prev_ts = str(prev_time_val)
                        end_ts = str(time_val)
                        if prev_ts not in ts_nametags:
                            ts_nametags[prev_ts] = {}
                            ts_nametags[prev_ts]['PROGRAM_ENDTIME'] = end_ts 
                        elif ts_nametags[prev_ts]['PROGRAM_ENDTIME'] == 0:
                            ts_nametags[prev_ts]['PROGRAM_ENDTIME'] = end_ts 
                        ts_nametags[prev_ts][result_key] = True
                        prev_time_val = time_val
                if last_time_val is not None:
                    prev_ts = str(prev_time_val)
                    end_ts = str(last_time_val)
                    if prev_ts not in ts_nametags:
                        ts_nametags[prev_ts] = {}
                        ts_nametags[prev_ts]['PROGRAM_ENDTIME'] = end_ts 
                    elif ts_nametags[prev_ts]['PROGRAM_ENDTIME'] == 0:
                        ts_nametags[prev_ts]['PROGRAM_ENDTIME'] = end_ts 
                    ts_nametags[prev_ts][result_key] = True
                 

            elif command == 'REGEX_TS':
                if not os.path.isfile(targetfile):
                    continue
                with open(targetfile, "r") as fh:
                    targetlines = fh.readlines()
                for currentline in targetlines:
                    sobj = re.search(lookupstring, currentline)
                    if sobj is not None:
                        time_val = getTS(currentline)
                        if time_val is None:
                            continue
                        ts = str(time_val)
                        if ts not in ts_nametags:
                            ts_nametags[ts] = {}
                            ts_nametags[ts]['PROGRAM_ENDTIME'] = 0
                        if field_type == 'GROUP':
                            try:
                                token = sobj.group(int(token_id))
                            except:
                                token = ''
                        else:
                            token = getToken(currentline, field_type, token_id, logger)
                        ts_nametags[ts][result_key] = token
            elif command == 'FILE_REGEX_TS':
                if not os.path.isfile(targetfile):
                    continue
                with open(targetfile, "r") as fh:
                    targetlines = fh.readlines()
                for currentline in targetlines:
                    sobj = re.search(lookupstring, currentline)
                    if sobj is not None:
                        time_val = getTS(currentline)
                        if time_val is None:
                            continue
                        ts = str(time_val)
                        if ts not in ts_nametags:
                            ts_nametags[ts] = {}
                            ts_nametags[ts]['PROGRAM_ENDTIME'] = 0
                        ts_nametags[ts][result_key] = True

    jsonoutput = open(ts_jsonfname, "w")
    for ts in ts_nametags:
        for key in ts_nametags[ts]:
            old = ts_nametags[ts][key]
            new = repr(old)
            ts_nametags[ts][key] = new
    try:
        jsondumpsoutput = json.dumps(ts_nametags, indent=4)
    except:
        logger.ERROR('json dumps failed on %s' % ts_nametags)
        exit(1)
    #print('dumping %s' % str(jsondumpsoutput))
    jsonoutput.write(jsondumpsoutput)
    jsonoutput.write('\n')
    jsonoutput.close()

def doFileTimeDelim(ts_nametags, result_home, targetfile, result_key, command, lookupstring, logger):
    fname, delim_prog = targetfile.split(':')
    logger.DEBUG('targetfile is time delim %s delim_prog %s fname %s' % (targetfile, delim_prog, fname))
    look_for = os.path.join(result_home,'%s.stdout.*' % delim_prog)
    #print('look for %s' % look_for)
    delim_list = glob.glob(look_for)
    delim_ts_set = []
    for delim_ts_file in delim_list:
        ts = delim_ts_file.rsplit('.',1)[1]
        delim_ts_set.append(ts)
    if len(delim_ts_set) == 0:
        logger.DEBUG('no ts files for program time delimiter %s' % delim_prog)
        return
    delim_ts_set.sort()
    end_times='99999999999999'
    delim_ts_set.append(end_times)
    ts = 0
    current_ts_end = delim_ts_set[0]
    index = 0
    with open(fname) as fh:
        for currentline in fh:
            time_val = getTS(currentline)
            logger.DEBUG('ts[index] %s  my_time %s' % (delim_ts_set[index], time_val))
            if time_val > delim_ts_set[index]:
                logger.DEBUG('time greater')
                if ts in ts_nametags:
                    ts_nametags[ts]['PROGRAM_ENDTIME'] = time_val
                ts = delim_ts_set[index]
                index += 1
            if command == 'CONTAINS':
                if lookupstring in currentline:
                    if ts not in ts_nametags:
                        ts_nametags[ts] = {}
                        ts_nametags[ts]['PROGRAM_ENDTIME'] = end_times
                    ts_nametags[ts][result_key] = True
            elif command == 'FILE_REGEX':
                remain = line.split(command,1)[1]
                remain = remain.split(':', 1)[1].strip()
                sobj = re.search(remain, currentline)
                if sobj is not None:
                    if ts not in ts_nametags:
                        ts_nametags[ts] = {}
                        ts_nametags[ts]['PROGRAM_ENDTIME'] = end_times
                    ts_nametags[ts][result_key] = True

def ParseConfigForTimeDelim(studentlabdir, labidname, configfilelines, ts_jsonfname, container_list, logger, parameter_list):
    '''
    Handle case of timestamped log files whose names are qualified by a 
    "time delimiter" program whose start times will
    be used to break up a timestamped log file.  The quantity of timestamped groupings
    of log file results will be one plus the quantity of invocations of the "time delimeter" program.
    '''
    ts_nametags = {}
    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip is not None and not linestrip.startswith('#') and len(line.strip())>0:
            containername, targetfile, result_key, command, field_type, token_id, lookupstring, result_home = getConfigItems(labidname, linestrip, studentlabdir, container_list, logger, parameter_list)
            if targetfile is not None and ':' in targetfile:
                 doFileTimeDelim(ts_nametags, result_home, targetfile, result_key, command, lookupstring, logger)
            elif targetfile is not None and command == 'TIME_DELIM':
                ''' set of timestamped values delimited by named program '''
                logger.DEBUG('targetfile is time delim %s ' % (targetfile))
                look_for = os.path.join(result_home,'%s.stdout.*' % targetfile)
                #print('look for %s' % look_for)
                delim_list = glob.glob(look_for)
                delim_ts_set = []
                prev_ts = 0
                for delim_ts_file in sorted(delim_list):
                    end_time='99999999999999'
                    ts = delim_ts_file.rsplit('.',1)[1]
                    if ts not in ts_nametags:
                        ts_nametags[ts] = {}
                        ts_nametags[ts]['PROGRAM_ENDTIME'] = end_time
                    ts_nametags[ts][result_key] = True
                    if prev_ts != 0:
                        ts_nametags[prev_ts]['PROGRAM_ENDTIME'] = ts
                    prev_ts = ts


    if len(ts_nametags) > 0:
        jsonoutput = open(ts_jsonfname, "w")
        for ts in ts_nametags:
            for key in ts_nametags[ts]:
                old = ts_nametags[ts][key]
                new = repr(old)
                ts_nametags[ts][key] = new
        try:
            jsondumpsoutput = json.dumps(ts_nametags, indent=4)
        except:
            logger.ERROR('json dumps failed on %s' % ts_nametags)
            exit(1)
        jsonoutput.write(jsondumpsoutput)
        jsonoutput.write('\n')
        jsonoutput.close()


def ParseConfigForFile(studentlabdir, labidname, configfilelines, 
                       outputjsonfname, container_list, timestamppart, end_time, logger, parameter_list):
    '''
    Invoked for each timestamp to parse results for that timestamp.
    Each config file line is assessed against each results file that corresponds
    to the given timestamp.  If timestamp is None, then look at all files that
    match the name found in the configuration file line, (e.g., for log files
    without timestamps.)
    '''
    #print('in ParseConfigForFile outputjsonfile: %s timestamppart %s' % (outputjsonfname, timestamppart))
    nametags = {}
    got_one = False
    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip is not None and not linestrip.startswith('#') and len(line.strip())>0:
            got_one = got_one | handleConfigFileLine(labidname, linestrip, nametags, studentlabdir, 
                                  container_list, timestamppart, logger, parameter_list)

    if end_time is not None:
        program_end_time = end_time
    else:
        program_end_time = 'NONE'
    if got_one:
        nametags['PROGRAM_ENDTIME'] = program_end_time
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
def ParseValidateResultConfig(actual_parsing, homedir, studentlabdir, container_list, labidname, logger_in, parameter_list):
    bool_results = []
    MYHOME = homedir
    logger = logger_in
    configfilename = os.path.join(MYHOME,'.local','instr_config', 'results.config')
    configfile = open(configfilename)
    configfilelines = configfile.readlines()
    configfile.close()

    for line in configfilelines:
        linestrip = line.rstrip()
        if linestrip:
            if not linestrip.startswith('#'):
                #print "Current linestrip is (%s)" % linestrip
                try:
                    (result_key, result_value) = linestrip.split('=', 1)
                except:
                     logger.ERROR('missing "=" character in %s' % linestrip)
                     sys.exit(1)
                result_key = result_key.strip()
                progname_type, cmd = ProcessConfigLine(actual_parsing, studentlabdir, container_list, labidname, result_key, result_value, logger)
                if result_key not in resultidlist:
                    resultidlist[result_key] = progname_type
                if cmd == 'CONTAINS' or (cmd is not None and cmd.startswith('FILE_REGEX')):
                    bool_results.append(result_key.strip())
        #else:
        #    print "Skipping empty linestrip is (%s)" % linestrip

    return configfilelines, resultidlist, bool_results

def ParseStdinStdout(homedir, studentlabdir, container_list, instructordir, labidname, logger_in):
    MYHOME = homedir
    logger = logger_in
    actual_parsing = True
    ''' pick a container directory from which to extract the lab instance seed for parameter replacements '''
    container_dir = os.path.join(studentlabdir, container_list[0])
    parameter_list = getParamList(MYHOME, container_dir, logger)
    # Parse and Validate Results configuration file
    configfilelines, resultlist, bool_results = ParseValidateResultConfig(actual_parsing, homedir, studentlabdir, 
                                      container_list, labidname, logger_in, parameter_list)

    ''' process all results files (ignore name of function) for a student.  These
        are distrbuted amongst multiple containers, per container_list.
    '''
    jsonoutputfilename = labidname
    #print("ParseStdinStdout: jsonoutputfilename is (%s) studentlabdir %s" % (jsonoutputfilename, studentlabdir))
    logger.DEBUG("ParseStdinStdout: jsonoutputfilename is (%s) studentlabdir %s" % (jsonoutputfilename, studentlabdir))

    timestamplist.clear()

    #del exec_proglist[:]
    del stdoutfnameslist[:]

    #print "exec_proglist is: "
    #print exec_proglist
    OUTPUTRESULTHOME = '%s/%s' % (studentlabdir, ".local/result/")
    logger.DEBUG('Done with validate, outputresult to %s' % OUTPUTRESULTHOME)

    if not os.path.exists(OUTPUTRESULTHOME):
        os.makedirs(OUTPUTRESULTHOME)

    bool_results_file = os.path.join(OUTPUTRESULTHOME, 'bool_results.json')
    with open(bool_results_file, 'w') as fh:
        json.dump(bool_results, fh, indent=4)

    '''
    A round-about-way of getting all time stamps
    '''
    for mycontainername in container_list:
        RESULTHOME = '%s/%s/%s' % (studentlabdir, mycontainername, ".local/result/")
        logger.DEBUG('check results for %s' % RESULTHOME)
        if not os.path.exists(RESULTHOME):
            ''' expected, some containers don't have local results '''
            logger.DEBUG('result directory %s does not exist' % RESULTHOME)
            pass
            
        if mycontainername not in container_exec_proglist:
            logger.DEBUG('%s not in proglist %s' % (mycontainername, str(container_exec_proglist)))
            continue

        for exec_prog in container_exec_proglist[mycontainername]:
            stdinfiles = '%s%s.%s.' % (RESULTHOME, exec_prog, "stdin")
            stdoutfiles = '%s%s.%s.' % (RESULTHOME, exec_prog, "stdout")
            prgoutfiles = '%s%s.%s.' % (RESULTHOME, exec_prog, "prgout")
            logger.DEBUG('stdin %s stdout %s' % (stdinfiles, stdoutfiles))
            globstdinfnames = glob.glob('%s*' % stdinfiles)
            globstdoutfnames = glob.glob('%s*' % stdoutfiles)
            if globstdoutfnames != []:
                #print "stdoutfnameglob list is "
                #print globstdoutfnames
                for stdoutfnames in globstdoutfnames:
                    #print stdoutfnames
                    stdoutfnameslist.append(stdoutfnames)
            globprgoutfnames = glob.glob('%s*' % prgoutfiles)
            if globprgoutfnames != []:
                for prgoutfnames in globprgoutfnames:
                    stdoutfnameslist.append(prgoutfnames)

        for stdoutfname in stdoutfnameslist:
            # the only purpose of this is to establish the timestamp dictionary
            # only stdout is looked at.
            #print('for stdout %s' % stdoutfname)
            for exec_prog in container_exec_proglist[mycontainername]:
                stdoutfiles = '%s%s.%s.' % (RESULTHOME, exec_prog, "stdout")
                if stdoutfiles in stdoutfname:
                    #print "match"
                    (filenamepart, timestamppart) = stdoutfname.split(stdoutfiles)
                    targetmtime = os.path.getmtime(stdoutfname)
                    if timestamppart not in timestamplist:
                        #print('adding %s' % timestamppart)
                        timestamplist[timestamppart] = targetmtime
                    elif targetmtime > timestamplist[timestamppart]:
                        timestamplist[timestamppart] = targetmtime
                else:
                    #print "no match"
                    continue

    ''' process each timestamped result file. '''
    for timestamppart in timestamplist:
        targetmtime_string = datetime.datetime.fromtimestamp(timestamplist[timestamppart])
        end_time = targetmtime_string.strftime("%Y%m%d%H%M%S")
        outputjsonfname = '%s%s.%s' % (OUTPUTRESULTHOME, jsonoutputfilename, timestamppart)
        logger.DEBUG("ParseStdinStdout (1): Outputjsonfname is (%s)" % outputjsonfname)
        ParseConfigForFile(studentlabdir, labidname, configfilelines, outputjsonfname, 
                           container_list, timestamppart, end_time, logger, parameter_list)
    ''' process files without timestamps '''
    outputjsonfname = '%s%s' % (OUTPUTRESULTHOME, jsonoutputfilename)
    ParseConfigForFile(studentlabdir, labidname, configfilelines, outputjsonfname, container_list, None, None, logger, parameter_list)
    ts_jsonfname = outputjsonfname+'_ts'
    ParseConfigForTimeRec(studentlabdir, labidname, configfilelines, ts_jsonfname, container_list, logger, parameter_list)
    td_jsonfname = outputjsonfname+'_td'
    ParseConfigForTimeDelim(studentlabdir, labidname, configfilelines, td_jsonfname, container_list, logger, parameter_list)



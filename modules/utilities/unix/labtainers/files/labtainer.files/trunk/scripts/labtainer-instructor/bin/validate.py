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

# Filename: validate.py
# Description:
# This is the validate script to be run by the instructor.
# Note:
# 1. It needs 'start.config' file, where
#    <labname> is given as a parameter to the script.
#

import getpass
import glob
import json
import md5
import os
import sys
import shutil

instructor_cwd = os.getcwd()
instructor_bin = os.path.join(instructor_cwd, 'assess_bin')
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
student_bin = os.path.join(student_cwd, 'lab_bin')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
sys.path.append(student_bin)
sys.path.append(instructor_bin)

import evalExpress
import labutils
import logging
import GoalsParser
import LabtainerLogging
import ParseStartConfig
import ParameterParser
import ResultParser

# TEMPORARY PATH - to copy 'config' and 'instr_config' to validate
TEMPDIR="/tmp/vallabtainers"

executefilelist = []

boolean_tokens = ['(',')','and_not', 'AND_NOT', 'or_not', 'OR_NOT', 'not','NOT','and','AND','or','OR','True','False']


def validate_parameter_result(parameter_list, resultidlist, goals, inputtag):
    validate_ok = True
    use_target = ""
    if "." in inputtag:
        (use_target, inputtagstring) = inputtag.split('.')
    if use_target == "":
        use_target = "result"
        inputtagstring = inputtag
    if use_target == "parameter" or use_target == "parameter_ascii":
        if inputtagstring not in parameter_list:
            validate_ok = False
    elif use_target == "result":
        if inputtagstring not in resultidlist:
            # handle expression here
            if inputtagstring.startswith('(') and inputtagstring.endswith(')'):
                express = inputtagstring[inputtagstring.find("(")+1:inputtagstring.find(")")]
                for tag in resultidlist:
                    labutils.logger.DEBUG('is tag %s in express %s' % (tag, express))
                    if tag in express:
                        # Replace each occurence of tag with 2
                        express = express.replace(tag, "2")
                try:
                    labutils.logger.DEBUG('try eval of <%s>' % express)
                    result = evalExpress.eval_expr(express)
                except:
                    labutils.logger.ERROR('could not evaluation %s, which became %s' % (inputtagstring, express))
                    validate_ok = False
            else:
                labutils.logger.ERROR('invalid tag %s' % inputtagstring)
                validate_ok = False
    else:
        validate_ok = False
    return validate_ok

def check_count(parameter_list, resultidlist, goals, jsongoalid, jsonresulttag):
    found_error = False
    # Make sure the resulttag is valid - no special case for resulttag
    validate_resulttag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonresulttag)
    if not validate_resulttag_ok:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid resulttag (%s)" % (jsongoalid, jsonresulttag))

    if not validate_resulttag_ok:
        found_error = True
    return found_error

def check_countgreater(parameter_list, resultidlist, goals, jsongoalid, jsonanswertag, boolean_string):
    found_error = False
    try:
        value = int(jsonanswertag)
    except:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid int (%s)" % (jsongoalid, jsonanswertag))
    # boolean_string must start with '(' and end with ')'
    # and contains comma separated goals
    validate_ok = True
    if boolean_string.startswith('(') and boolean_string.endswith(')'):
        express = boolean_string[boolean_string.find("(")+1:boolean_string.find(")")]
        for tag in express.split(','):
            goaltag = tag.strip()
            # goaltag must be in goals otherwise it is an error
            found_goaltag_in_goals = False
            if goaltag in resultidlist:
                found_goaltag_in_goals = True
            else:
                for eachgoal in goals:
                    if goaltag == eachgoal['goalid']:
                        found_goaltag_in_goals = True
                        break
            if found_goaltag_in_goals:
                continue
            else:
                labutils.logger.ERROR('invalid goal %s in %s' % (goaltag, boolean_string))
                validate_ok = False
                break
    else:
        labutils.logger.ERROR('ERROR: expected goals %s in parens' % boolean_string)
        validate_ok = False
    if not validate_ok:
        found_error = True
    return found_error

def check_temporal(parameter_list, resultidlist, goals, jsongoalid, goal1tag, goal2tag):
    found_error = False
    goal1tag_ok = True
    goal2tag_ok = True
    # goal1tag must be in goals, or a result name (TBD should only be booleans) otherwise it is an error
    found_goaltag_in_goals = False
    if goal1tag in resultidlist:
        found_goaltag_in_goals = True
    else:
        for eachgoal in goals:
            if goal1tag == eachgoal['goalid']:
                found_goaltag_in_goals = True
                break
    if not found_goaltag_in_goals:
        goal1tag_ok = False
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid goal1tag (%s)" % (jsongoalid, goal1tag))
    # goal2tag must be in goals otherwise it is an error
    found_goaltag_in_goals = False
    if goal2tag in resultidlist:
        found_goaltag_in_goals = True
    else:
        for eachgoal in goals:
            if goal2tag == eachgoal['goalid']:
                found_goaltag_in_goals = True
                break
    if not found_goaltag_in_goals:
        goal2tag_ok = False
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid goal2tag (%s)" % (jsongoalid, goal2tag))
    if not (goal1tag_ok and goal2tag_ok):
        found_error = True
    return found_error

def check_boolean(parameter_list, bool_results, goals, jsongoalid, boolean_string):
    found_error = False
    # Make it easier to tokenize later
    boolean_string = boolean_string.replace('(', ' ( ')
    boolean_string = boolean_string.replace(')', ' ) ').strip()
    # boolean_string must start with '(' and end with ')'
    # must be token separated goals
    validate_ok = True
    if boolean_string.startswith('(') and boolean_string.endswith(')'):
        for tag in boolean_string.split():
            goaltag = tag.strip()
            # if goaltag is valid boolean operator, skip
            if goaltag in boolean_tokens:
                continue
            # goaltag must be in goals otherwise it is an error
            found_goaltag_in_goals = False
            if goaltag in bool_results:
                found_goaltag_in_goals = True
            else:
                for eachgoal in goals:
                    if goaltag == eachgoal['goalid']:
                        found_goaltag_in_goals = True
                        break
            if found_goaltag_in_goals:
                continue
            else:
                labutils.logger.ERROR('invalid goal %s in %s' % (goaltag, boolean_string))
                validate_ok = False
                break
    else:
        labutils.logger.ERROR('ERROR: expected goals %s in parens' % boolean_string)
        validate_ok = False
    if not validate_ok:
        found_error = True
    return found_error

def check_execute(parameter_list, resultidlist, goals, jsongoalid, executefilepath, jsonanswertag, jsonresulttag):
    found_error = False
    executefile = os.path.basename(executefilepath)
    executefile_ok = True
    if executefile not in executefilelist:
        executefile_ok = False

    validate_answertag_ok = True
    # Make sure the answertag is valid
    # Handle special case 'answer=<string>'
    if '=' in jsonanswertag:
        # skip it
        validate_answertag_ok = True
    else:
        validate_answertag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonanswertag)
    if not validate_answertag_ok:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid answertag (%s)" % (jsongoalid, jsonanswertag))

    # Make sure the resulttag is valid - resulttag can't have 'answer=<string>'
    validate_resulttag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonresulttag)
    if not validate_resulttag_ok:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid resulttag (%s)" % (jsongoalid, jsonresulttag))

    if not (executefile_ok and validate_answertag_ok and validate_resulttag_ok):
        found_error = True
       
    return found_error

def check_matches(parameter_list, resultidlist, goals, jsongoalid, jsonanswertag, jsonresulttag):
    found_error = False
    validate_answertag_ok = True
    # Make sure the answertag is valid
    # Handle special case 'answer=<string>'
    if '=' in jsonanswertag:
        # skip it
        validate_answertag_ok = True
    else:
        validate_answertag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonanswertag)
    if not validate_answertag_ok:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid answertag (%s)" % (jsongoalid, jsonanswertag))

    validate_resulttag_ok = True
    # Make sure the resulttag is valid - no special case for resulttag
    validate_resulttag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonresulttag)
    if not validate_resulttag_ok:
        labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid resulttag (%s)" % (jsongoalid, jsonresulttag))

    if not (validate_answertag_ok and validate_resulttag_ok):
        found_error = True

    return found_error

def validate_goals(parameter_list, resultidlist, goals, bool_results):
    #labutils.logger.DEBUG("Result ID list is ")
    #labutils.logger.DEBUG(resultidlist)
    #labutils.logger.DEBUG("Parameter list is ")
    #labutils.logger.DEBUG(parameter_list)
    #labutils.logger.DEBUG("Goals list is ")
    #labutils.logger.DEBUG(goals)
    found_error = False
    for eachgoal in goals:
        #labutils.logger.DEBUG("Current goal is ")
        #labutils.logger.DEBUG(eachgoal)
        #labutils.logger.DEBUG("    goalid is (%s)" % eachgoal['goalid'])
        #labutils.logger.DEBUG("    goaltype is (%s)" % eachgoal['goaltype'])
        #labutils.logger.DEBUG("    answertag is (%s)" % eachgoal['answertag'])
        #labutils.logger.DEBUG("    resulttag is (%s)" % eachgoal['resulttag'])
        jsongoalid = eachgoal['goalid']
        jsongoaltype = eachgoal['goaltype']

        found_error = False
        if (jsongoaltype == "matchany" or
            jsongoaltype == "matchlast" or
            jsongoaltype  == "matchacross"):
            jsonanswertag = eachgoal['answertag']
            jsonresulttag = eachgoal['resulttag']
            found_error = check_matches(parameter_list, resultidlist, goals, jsongoalid, jsonanswertag, jsonresulttag)
        elif jsongoaltype == "execute":
            executefilepath = eachgoal['goaloperator']
            jsonanswertag = eachgoal['answertag']
            jsonresulttag = eachgoal['resulttag']
            found_error = check_execute(parameter_list, resultidlist, goals, jsongoalid, executefilepath, jsonanswertag, jsonresulttag)
        elif jsongoaltype == "boolean":
            boolean_string = eachgoal['boolean_string']
            found_error = check_boolean(parameter_list, bool_results, goals, jsongoalid, boolean_string)
        elif jsongoaltype == "time_before" or jsongoaltype == "time_during" or jsongoaltype == "time_not_during":
            goal1tag = eachgoal['goal1tag']
            goal2tag = eachgoal['goal2tag']
            found_error = check_temporal(parameter_list, resultidlist, goals, jsongoalid, goal1tag, goal2tag)
        elif jsongoaltype == "count_greater":
            boolean_string = eachgoal['boolean_string']
            jsonanswertag = eachgoal['answertag']
            found_error = check_countgreater(parameter_list, resultidlist, goals, jsongoalid, jsonanswertag, boolean_string)
        elif jsongoaltype == "count" or jsongoaltype == "value":
            jsonresulttag = eachgoal['resulttag']
            found_error = check_count(parameter_list, resultidlist, goals, jsongoalid, jsonresulttag)
        elif jsongoaltype.startswith('is_'):
            jsonresulttag = eachgoal['resulttag']
            validate_resulttag_ok = validate_parameter_result(parameter_list, resultidlist, goals, jsonresulttag)
            if not validate_resulttag_ok:
                found_error = True
                labutils.logger.ERROR("ERROR: Goals goalid (%s) has invalid resulttag (%s)" % (jsongoalid, jsonresulttag))
        else:
            sys.stdout.write("Error: Invalid goal type: %s\n eachgoal is %s" % (jsongoaltype, str(eachgoal)))
            sys.exit(1)

        # Found an error - break for loop
        if found_error:
            return False
    if not found_error:
        return True
    else:
        return False


def setup_to_validate(lab_path, labname, validatetestsets, validatetestsets_path, logger):
    # Create TEMPDIR - remove if it exists
    if os.path.exists(TEMPDIR):
        shutil.rmtree(TEMPDIR)
    TEMPLOCAL = os.path.join(TEMPDIR, ".local")
    TEMPLOCALBIN = os.path.join(TEMPDIR, ".local", "bin")
    os.makedirs(TEMPLOCAL)
    os.makedirs(TEMPLOCALBIN)

    # Pick arbitrary e-mail
    user_email = "validate%s@dummy.org" % labname
    #config_path       = os.path.join(lab_path,"config") 
    #start_config_path = os.path.join(config_path,"start.config")
    #start_config = ParseStartConfig.ParseStartConfig(start_config_path, labname, "instructor", labutils.logger)
    labtainer_config, start_config = labutils.GetBothConfigs(lab_path, 'instructor', logger)

   
    # Warns if xterm has no instruction.txt file
    for container_name, container in start_config.containers.items():
        if container.xterm is not None:
            # instruction.txt file path
            instruction_path = "%s/%s/instructions.txt" % (lab_path, container_name)
            if not (os.path.exists(instruction_path) and os.path.isfile(instruction_path)):
                logger.WARNING("container %s instruction_path file %s not found" % (container_name, instruction_path))

    lab_master_seed = start_config.lab_master_seed
    # Create hash using LAB_MASTER_SEED concatenated with user's e-mail
    # LAB_MASTER_SEED is per laboratory - specified in start.config
    string_to_be_hashed = '%s:%s' % (lab_master_seed, user_email)
    mymd5 = md5.new()
    mymd5.update(string_to_be_hashed)
    lab_instance_seed = mymd5.hexdigest()
    labutils.logger.DEBUG("seed %s" % lab_instance_seed)

    # Create files
    LAB_SEEDFILE = os.path.join(TEMPLOCAL, ".seed")
    with open(LAB_SEEDFILE, "w") as fh:
        fh.write("%s\n" % lab_instance_seed)
    fh.close()
    USER_EMAILFILE = os.path.join(TEMPLOCAL, ".email")
    with open(USER_EMAILFILE, "w") as fh:
        fh.write("%s\n" % user_email)
    fh.close()
    LAB_NAMEFILE = os.path.join(TEMPLOCAL, ".labname")
    with open(LAB_NAMEFILE, "w") as fh:
        fh.write("%s\n" % labname)
    fh.close()
    WATERMARK_NAMEFILE = os.path.join(TEMPLOCAL, ".watermark")
    string_to_be_hashed = '%s:%s' % (lab_instance_seed, user_email)
    mymd5 = md5.new()
    mymd5.update(string_to_be_hashed)
    watermark = mymd5.hexdigest()
    labutils.logger.DEBUG("watermark %s" % watermark)
    with open(WATERMARK_NAMEFILE, "w") as fh:
        fh.write("%s\n" % watermark)
    fh.close()

    # Copy 'config' and 'instr_config' from LABPATH to TEMPLOCAL
    LAB_CONFIG = os.path.join(lab_path, "config")
    LAB_INSTRCONFIG = os.path.join(lab_path, "instr_config")
    TEMP_LAB_CONFIG = os.path.join(TEMPLOCAL, "config")
    TEMP_LAB_INSTRCONFIG = os.path.join(TEMPLOCAL, "instr_config")
    shutil.copytree(LAB_CONFIG, TEMP_LAB_CONFIG)
    shutil.copytree(LAB_INSTRCONFIG, TEMP_LAB_INSTRCONFIG)
    # If we are doing validatetestsets - replace the three config files
    if validatetestsets:
        parameterconfig_path = os.path.join(validatetestsets_path, "parameter.config")
        resultsconfig_path = os.path.join(validatetestsets_path, "results.config")
        goalsconfig_path = os.path.join(validatetestsets_path, "goals.config")
        target_parameterconfig_path = os.path.join(TEMP_LAB_CONFIG, "parameter.config")
        target_resultsconfig_path = os.path.join(TEMP_LAB_INSTRCONFIG, "results.config")
        target_goalsconfig_path = os.path.join(TEMP_LAB_INSTRCONFIG, "goals.config")
        shutil.copy(parameterconfig_path, target_parameterconfig_path)
        shutil.copy(resultsconfig_path, target_resultsconfig_path)
        shutil.copy(goalsconfig_path, target_goalsconfig_path)

    # Get a list of any executable in '_bin' directory
    # except fixlocal.sh, treataslocal, startup.sh
    binfilelist = glob.glob("%s/*/_bin/*" % lab_path)
    for binfilepath in binfilelist:
        binfilename = os.path.basename(binfilepath)
        if not (binfilename == "fixlocal.sh" or 
                binfilename == "treataslocal" or
                binfilename == "startup.sh"):
            if binfilename not in executefilelist:
                executefilelist.append(binfilename)
                shutil.copy(binfilepath, TEMPLOCALBIN)

    email_labname = "%s.%s" % (user_email.replace("@","_at_"), labname)

    container_list = []
    container_list.append(start_config.grade_container)
    for name, container in start_config.containers.items():
        if container.full_name not in container_list:
            container_list.append(container.full_name)

    return lab_instance_seed, container_list, email_labname

# Validate resultidlist for 'system' in 'treataslocal'
def ValidateTreataslocal(labname, lab_path, resultidlist, logger):
    checklist = []
    for key, progname_type in resultidlist.iteritems():
        if ':' in progname_type:
            #container_name, newprogname_type = progname_type.split(':')
            container_name = labname
            parts = progname_type.split(':')
            if len(parts) == 2:
                if parts[0].startswith('/'):
                    newprogname_type =  parts[0]
                else:
                    container_name = parts[0]
                    newprogname_type = parts[1]
            elif len(parts) == 3:
                container_name = parts[0]
                newprogname_type = parts[1]
        else:
            container_name = labname
            newprogname_type = progname_type
        if newprogname_type.startswith('*'):
            # start with wildcard, skip
            continue
        if newprogname_type.endswith('stdin') or newprogname_type.endswith('stdout'):
            execprog, type = newprogname_type.rsplit('.', 1)
            if execprog == "precheck":
                # skip checklocal
                continue
        else:
            # skipping non stdin/stdout
            continue

        if execprog in checklist:
            # already checked before, skip
            continue

        # Test for execprog using which locally
        command = "which %s > /dev/null" % execprog

        checklist.append(execprog)

        # If os.system(command) is zero, i.e., success then
        if os.system(command) == 0:
            # Test against corresponding container's treataslocal file (loop through to check)
            treataslocal_path = "%s/%s/_bin/treataslocal" % (lab_path, container_name)
            if not (os.path.exists(treataslocal_path) and os.path.isfile(treataslocal_path)):
                logger.WARNING("treataslocal file %s not found when validating command %s from %s %s" % (treataslocal_path, execprog, key, progname_type))
                user_input=raw_input("Would you like to quit? (yes/no)\n")
                user_input=user_input.strip().lower()
                #print "user_input (%s)" % user_input
                if user_input == "yes":
                    sys.exit(1)
            with open(treataslocal_path) as fh:
                 execlist_from_file = [os.path.basename(line.strip()) for line in fh]
            if not execprog in execlist_from_file:
                 logger.ERROR("treataslocal path %s in treataslocal" % treataslocal_path)
                 logger.ERROR("result id (%s) has exec program %s not found in treataslocal" % (key, execprog))
                 sys.exit(1)

def DoValidate(lab_path, labname, validatetestsets, validatetestsets_path, logger):
    labutils.is_valid_lab(lab_path)

    lab_instance_seed, container_list, email_labname = setup_to_validate(lab_path, labname, validatetestsets, validatetestsets_path, logger)
    logger.DEBUG("container_list (%s)" % container_list)
 
    LabDirName = os.path.join(TEMPDIR, email_labname)
    # Just validating - not actual parsing
    actual_parsing = False
    configfilelines, resultidlist, bool_results = ResultParser.ParseValidateResultConfig(actual_parsing, TEMPDIR, LabDirName, container_list, labname, logger)

    # Validate resultidlist for 'system' in 'treataslocal'
    ValidateTreataslocal(labname, lab_path, resultidlist, logger)

    parameter_list = GoalsParser.ParseGoals(TEMPDIR, TEMPDIR, logger)
    # GoalsParser created goals.json in parent directory
    parent_dir = os.path.dirname(TEMPDIR)
    goalsjsonfname = os.path.join(parent_dir, '.local','result','goals.json')
    goalsjson = open(goalsjsonfname, "r")
    goals = json.load(goalsjson)
    goalsjson.close()
    #logger.DEBUG("Goals JSON config is")
    #logger.DEBUG(goals)

    return validate_goals(parameter_list, resultidlist, goals, bool_results)

# Usage: validate.py <labname> | -c <validatetestsetsname>
#    -c <validatetestsetsname> to run validate.py against <validatetestsetsname>
def main():
    num_args = len(sys.argv)
    if num_args < 2 or num_args > 3:
        sys.stderr.write("Usage: validate.py <labname> | -c <validatetestsetsname>\n")
        sys.stderr.write("   -c <validatetestsetsname> to run validate.py against <validatetestsetsname>.\n")
        sys.exit(1)
    validatetestsets = False
    validatetestsets_path = ""
    if num_args == 2:
        labname = sys.argv[1]
        validatetestsetsname = "NONE"
    else:
        validatetestsets = True
        validatetestsetsname = sys.argv[2]
        dir_path = os.path.dirname(os.path.realpath(__file__))
        dir_path = dir_path[:dir_path.index("scripts")]
        validatetestsets_path = os.path.join(dir_path, "testsets", "validate", validatetestsetsname)
        print "current path is (%s)" % validatetestsets_path
        labname_path = os.path.join(validatetestsets_path, "labname")
        if not (os.path.exists(labname_path) and os.path.isfile(labname_path)):
            sys.stderr.write("labname file for %s does not exists!\n" % validatetestsetsname)
            sys.exit(1)
        else:
            with open(labname_path) as fh:
                labname = fh.read().strip()
        
    labutils.logger = LabtainerLogging.LabtainerLogging("labtainer.log", labname, "../../config/labtainer.config")
    labutils.logger.INFO("Begin logging validate.py for %s lab" % labname)
    labutils.logger.DEBUG("Instructor CWD = (%s), Student CWD = (%s)" % (instructor_cwd, student_cwd))
    lab_path = os.path.join(os.path.abspath('../../labs'), labname)
    DoValidate(lab_path, labname, validatetestsets, validatetestsets_path, labutils.logger)
    return 0

if __name__ == '__main__':
    sys.exit(main())

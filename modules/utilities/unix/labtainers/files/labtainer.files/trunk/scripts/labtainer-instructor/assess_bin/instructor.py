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

# Instructor.py
# Description: * Read instructorlab.json and extract a zip file
#                containing the student lab work
#              * Call script to grade the student lab work

import copy
import json
import md5
import os
import sys
import zipfile
import time
import glob
import shutil
import GenReport
import Grader
import GoalsParser
import ResultParser
import UniqueCheck
import InstructorLogging
import string
import LabCount

MYHOME=os.getenv('HOME')
logger = InstructorLogging.InstructorLogging("/tmp/instructor.log")


def newStudentJson():
        student_json = {}
        student_json['parameter'] = {}
        student_json['grades'] = {}
        student_json['firstlevelzip'] = {}
        student_json['secondlevelzip'] = {}
        student_json['actualwatermark'] = {}
        student_json['expectedwatermark'] = {}
        student_json['labcount'] = {}
        return student_json

    

def store_student_labcount(gradesjson, email_labname, student_lab_count):
    logger.DEBUG('store_student_labcount email_labname %s' % (email_labname))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
    else:
        if gradesjson[email_labname]['labcount'] != {}:
            # Already have that student's labcount stored
            logger.ERROR("instructor.py store_student_labcount: duplicate email_labname %s labcount %s" % (email_labname, labcount))
            sys.exit(1)
    gradesjson[email_labname]['labcount'] = copy.deepcopy(student_lab_count)

def store_student_watermark(gradesjson, email_labname, actual_watermark, expected_watermark):
    logger.DEBUG('store_student_watermal email_labname %s actual %s expected %s' % (email_labname, actual_watermark, expected_watermark))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
    gradesjson[email_labname]['actualwatermark'] = actual_watermark
    gradesjson[email_labname]['expectedwatermark'] = expected_watermark

def store_student_firstlevelzip(gradesjson, email_labname, first_zip_name):
    logger.DEBUG('store_student_firstlevelzip email_labname %s first_zip_name %s' % (email_labname, first_zip_name))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
    gradesjson[email_labname]['firstlevelzip'] = first_zip_name

def store_student_secondlevelzip(gradesjson, email_labname, second_zip_name):
    logger.DEBUG('store_student_secondlevelzip email_labname %s second_zip_name %s' % (email_labname, second_zip_name))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
    gradesjson[email_labname]['secondlevelzip'] = second_zip_name

def store_student_parameter(gradesjson, email_labname, student_parameter):
    logger.DEBUG('store_student_parameter email_labname %s student_parameter %s' % (email_labname, student_parameter))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
        if gradesjson[email_labname]['parameter'] != {}:
            # Already have that student's parameter stored
            logger.ERROR("instructor.py store_student_parameter: duplicate email_labname %s student_parameter %s" % (email_labname, student_parameter))
            sys.exit(1)
    gradesjson[email_labname]['parameter'] = copy.deepcopy(student_parameter)

def store_student_grades(gradesjson, email_labname, grades):
    logger.DEBUG('store_student_grades email_labname %s grades %s' % (email_labname, grades))
    if email_labname not in gradesjson:
        gradesjson[email_labname] = newStudentJson()
        if gradesjson[email_labname]['grades'] != {}:
            # Already have that student's grades stored
            logger.ERROR("instructor.py store_student_grades: duplicate email_labname %s grades %s" % (email_labname, grades))
            sys.exit(1)
    gradesjson[email_labname]['grades'] = copy.deepcopy(grades)

def store_student_unique(uniquejson, email_labname, uniquevalues):
    logger.DEBUG('store_student_unique email_labname %s unique %s' % (email_labname, uniquevalues))
    if email_labname not in uniquejson:
        uniquejson[email_labname] = {}
        uniquejson[email_labname]['unique'] = copy.deepcopy(uniquevalues)
    else:
        if uniquejson[email_labname]['unique'] != {}:
            # Already have that student's unique values stored
            logger.ERROR("instructor.py store_student_unique: duplicate email_labname %s unique %s" % (email_labname, uniquevalues))
            sys.exit(1)
        else:
            uniquejson[email_labname]['unique'] = copy.deepcopy(uniquevalues)


# Make sure second level zip file e-mail is OK
def Check_SecondLevel_EmailWatermark_OK(gradesjson, email_labname, student_id, zipoutput):
    check_result = True
    TMPDIR = "/tmp/labtainer"
    TempEmailFile = "%s/.local/.email" % TMPDIR
    TempWatermarkFile = "%s/.local/.watermark" % TMPDIR
    TempSeedFile = "%s/.local/.seed" % TMPDIR
    # Remove Temporary Email file first then extract
    try:
        os.remove(TempEmailFile)
        os.remove(TempWatermarkFile)
        os.remove(TempSeedFile)
    except OSError:
        pass

    # Do not extract unnecessarily
    for zi in zipoutput.infolist():
        zname = zi.filename
        if zname == ".local/.email" or zname == ".local/.seed" or zname == ".local/.watermark":
            zipoutput.extract(zi, TMPDIR)

    with open(TempEmailFile) as fh:
        student_id_from_file = fh.read().strip().replace("@","_at_")

    # Student ID obtained from zip_file_name must match the one from E-mail file
    if not all(c in string.printable for c in student_id_from_file): 
        student_id_from_file = 'not_printable'
    if student_id != student_id_from_file:
        print "mismatch student_id is (%s) student_id_from_file is (%s)" % (student_id, student_id_from_file)
        store_student_secondlevelzip(gradesjson, email_labname, student_id_from_file)
        #check_result = False

    if os.path.exists(TempWatermarkFile):
        with open(TempWatermarkFile) as fh:
            actual_watermark = fh.read().strip()

        # Create watermark from hash of lab_instance_seed and the watermark string
        with open(TempSeedFile) as fh:
            seed_from_file = fh.read().strip()

        the_watermark_string = "LABTAINER_WATERMARK1"
        string_to_be_hashed = '%s:%s' % (seed_from_file, the_watermark_string)
        mymd5 = md5.new()
        mymd5.update(string_to_be_hashed)
        expected_watermark = mymd5.hexdigest()
        #print expected_watermark

        # Watermark must match
        if actual_watermark != expected_watermark:
            #print "mismatch actual is (%s) expected is (%s)" % (actual_watermark, expected_watermark)
            check_result = False
        # Store the actual and expected watermark regardless
        # So that when generating report, we can figure out the 'source' 
        store_student_watermark(gradesjson, email_labname, actual_watermark, expected_watermark)

    return check_result

# Usage: Instructor.py
# Arguments:
#   check_watermark - whether to do watermark checks or not
def main():
    #print "Running Instructor.py"
    #
    # TBD clean this up, break out routines.  impossible to follow.
    #

    logger.INFO("Begin logging instructor.py")

    # Default to check_watermark to True
    check_watermark = True
    logger.DEBUG('MYHOME is %s' % MYHOME)
    os.chdir(MYHOME)
    lab_name_dir = os.path.join(MYHOME,'.local','.labname')
    if not os.path.isfile(lab_name_dir):
        logger.ERROR('no file at %s, perhaps running instructor script on wrong containers?' % lab_name_dir)
        sys.exit(1)

    with open(lab_name_dir) as fh:
        lab_id_name = fh.read().strip()
    check_watermark_argument=None
    if len(sys.argv) > 1:
        check_watermark_argument = str(sys.argv[1]).upper()

        if check_watermark_argument == "TRUE":
            check_watermark = True
        elif check_watermark_argument == "FALSE":
            check_watermark = False
        else:
            logger.ERROR('Usage: instructor.py "[True|False]"')
            exit(1)

    # is this used?  
    InstructorBaseDir = os.path.join(MYHOME, '.local', 'base')

    ''' dictionary of container lists keyed by student email_labname '''
    student_list = {}
   
    # Store grades, goals, etc
    gradesjson = {}
    # Store Unique checks, etc
    uniquejson = {}

    ''' remove zip files in /tmp/labtainer directory '''
    # /tmp/labtainer will be used to store temporary zip files
    TMPDIR = "/tmp/labtainer"
    if os.path.exists(TMPDIR):
        # exists but is not a directory
        if not os.path.isdir(TMPDIR):
            # remove file then create directory
            os.remove(TMPDIR)
            os.makedirs(TMPDIR)
    else:
        # does not exists, create directory
        os.makedirs(TMPDIR)
    for tmpzip in glob.glob("%s/*.zip" % TMPDIR):
        os.remove(tmpzip)
    
    ''' unzip everything ''' 
    ''' First level unzip '''
    zip_files = glob.glob(MYHOME+'/*.zip')
    first_level_zip = []
    for zfile in zip_files:
        zip_file_name = os.path.basename(zfile)
        if zip_file_name.startswith('bulk_download'):
            continue
        orig_email_labname, orig_zipext = zip_file_name.rsplit('.', 1)
        first_level_zip.append(zip_file_name)
        OutputName = os.path.join(MYHOME, zip_file_name)
        zipoutput = zipfile.ZipFile(OutputName, "r")
        ''' retain dates of student files '''
        for zi in zipoutput.infolist():
            zname = zi.filename
            if not (zname.endswith('.zip') or \
               zname.endswith('.log') or \
               zname.endswith('.json')):
                continue
            if '=' in zname:
                second_email_labname, second_containername = zname.rsplit('=', 1)
                # Mismatch e-mail name at first level
                if orig_email_labname != second_email_labname:
                    store_student_firstlevelzip(gradesjson, orig_email_labname, second_email_labname)
                    # DO NOT process that student's zip file any further, i.e., DO NOT extract
                    print('DO NOT process that students zip file any further, i.e., DO NOT extract')
                    continue
            zipoutput.extract(zi, TMPDIR)
            date_time = time.mktime(zi.date_time + (0, 0, -1))
            dest = os.path.join(TMPDIR, zi.filename)
            os.utime(dest, (date_time, date_time))
        zipoutput.close()
    # Add docs.zip as a file to skip also
    first_level_zip.append('docs.zip')

    ''' Second level unzip '''
    zip_files = glob.glob(TMPDIR+'/*.zip')
    for zfile in zip_files:
        zip_file_name = os.path.basename(zfile)
        # Skip first level zip files
        if zip_file_name in first_level_zip:
            continue
        #print('zipfile is %s' % zip_file_name)
        DestinationDirName = os.path.splitext(zip_file_name)[0]
        if '=' in DestinationDirName:
            # NOTE: New format has DestinationDirName as:
            #       e-mail+labname '=' containername
            # get email_labname and containername
            email_labname, containername = DestinationDirName.rsplit('=', 1)
            # Replace the '=' to '/'
            DestinationDirName = '%s/%s' % (email_labname, containername)
            #print email_labname
        else:
            # Old format - no containername
            logger.ERROR("Instructor.py old format (no containername) no longer supported!\n")
            return 1
        student_id = email_labname.rsplit('.', 1)[0]
        #print "student_id is %s" % student_id
        logger.DEBUG("student_id is %s" % student_id)
        OutputName = '%s/%s' % (TMPDIR, zip_file_name)
        lab_dir_name = os.path.join(MYHOME, email_labname)
        DestDirName = os.path.join(MYHOME, DestinationDirName)
        InstDirName = os.path.join(InstructorBaseDir, DestinationDirName)
        src_count_path = os.path.join(TMPDIR, 'count.json')
        dst_count_path = LabCount.getPath(lab_dir_name, lab_id_name) 
        #print('dst_count_path is %s' % dst_count_path)
        if os.path.isfile(src_count_path):
            #  ad-hoc fix to remnants of old bug, remove this
            if os.path.isdir(dst_count_path):
                logger.WARNING('removing errored directory %s' % dst_count_path)
                shutil.rmtree(dst_count_path)
            parent = os.path.dirname(dst_count_path)
            #print('parent %s' % parent)
            try:
                os.makedirs(parent)
            except:
                pass
            #print('found count.json')
            shutil.copyfile(src_count_path, dst_count_path)

        #print "Student Lab list : "
        #print studentslablist

        if os.path.exists(DestDirName):
            #print "Removing %s" % DestDirName
            os.system('rm -rf %s' % DestDirName)

        zipoutput = zipfile.ZipFile(OutputName, "r")

        # Do Watermark checks only if check_watermark is True
        if check_watermark:
            # If e-mail mismatch, do not further extract the zip file
            if not Check_SecondLevel_EmailWatermark_OK(gradesjson, email_labname, student_id, zipoutput):
                # continue with next one
                continue

        # If no problem with e-mail, then continue processing
        if email_labname not in student_list:
            student_list[email_labname] = []
        student_list[email_labname].append(containername) 
        #print('append container %s for student %s' % (containername, email_labname))
        logger.DEBUG('append container %s for student %s' % (containername, email_labname))

        ''' retain dates of student files '''
        for zi in zipoutput.infolist():
            zipoutput.extract(zi, DestDirName)
            date_time = time.mktime(zi.date_time + (0, 0, -1))
            dest = os.path.join(DestDirName, zi.filename)
            os.utime(dest, (date_time, date_time))

        zipoutput.close()

    pregrade_script = os.path.join(MYHOME,'.local','instr_config', 'pregrade.sh')
    do_pregrade = False
    if os.path.isfile(pregrade_script):
        do_pregrade = True
    unique_check = os.path.join(MYHOME,'.local','instr_config', 'unique.config')
    do_unique = False
    if os.path.isfile(unique_check):
        do_unique = True
    ''' create per-student goals.json and process results for each student '''
    for email_labname in student_list:
        # GoalsParser is now tied per student - do this after unzipping file
        # Call GoalsParser script to parse 'goals'
        ''' note odd hack, labinstance seed is stored on container, so need to fine one, use first '''
        DestinationDirName = '%s/%s' % (email_labname, student_list[email_labname][0])
        DestDirName =os.path.join(MYHOME, DestinationDirName)
        # TBD also getting what, student parameters from first container.  
        # Better way to get instr_config files than do duplicate on each container?  Just put on grader? 
        student_parameter = GoalsParser.ParseGoals(MYHOME, DestDirName, logger)
       
        if do_pregrade:
            ''' invoke pregrade for each container '''
            for container in student_list[email_labname]: 
                dest = os.path.join(email_labname, container)
                cmd = '%s %s %s' % (pregrade_script, MYHOME, dest)
                logger.DEBUG('invoke pregrade script %s' % cmd)
                os.system(cmd) 

        ''' backward compatible for test sets '''
        for container in student_list[email_labname]:
            dest = os.path.join(email_labname, container)
            look_for = dest+'/.local/result/checklocal*'
            check_local_list = glob.glob(look_for)
            for cl in check_local_list:
                newname = cl.replace('checklocal', 'precheck')
                shutil.move(cl, newname)

        # Call ResultParser script to parse students' result
        lab_dir_name = os.path.join(MYHOME, email_labname)
        #print('call ResultParser for %s %s' % (email_labname, student_list[email_labname]))
        logger.DEBUG('call ResultParser for %s %s' % (email_labname, student_list[email_labname]))
        ResultParser.ParseStdinStdout(MYHOME, lab_dir_name, student_list[email_labname], InstDirName, lab_id_name, logger)

        # Add student's parameter
        store_student_parameter(gradesjson, email_labname, student_parameter)

        if do_unique:
            #print('call UniqueCheck for %s %s' % (email_labname, student_list[email_labname]))
            logger.DEBUG('call UniqueCheck for %s %s' % (email_labname, student_list[email_labname]))
            uniquevalues = UniqueCheck.UniqueCheck(MYHOME, lab_dir_name, student_list[email_labname], InstDirName, lab_id_name, logger)
            # Add student's unique check
            store_student_unique(uniquejson, email_labname, uniquevalues)

    ''' assess the results and generate simple report '''
    for email_labname in student_list:
        lab_dir_name = os.path.join(MYHOME, email_labname)
        grades = Grader.ProcessStudentLab(lab_dir_name, lab_id_name, logger)
        student_id = email_labname.rsplit('.', 1)[0]
        LabIDStudentName = '%s : %s : ' % (lab_id_name, student_id)

        # Add student's grades
        store_student_grades(gradesjson, email_labname, grades)

        # Add student's lab counter (if exists)
        student_lab_count = LabCount.getLabCount(lab_dir_name, lab_id_name, logger)
        store_student_labcount(gradesjson, email_labname, student_lab_count)

    #print "grades (in JSON) is "
    #print gradesjson

    # Output <labname>.grades.json
    gradesjsonname = os.path.join(MYHOME, "%s.grades.json" % lab_id_name)
    gradesjsonoutput = open(gradesjsonname, "w")
    try:
        jsondumpsoutput = json.dumps(gradesjson, indent=4)
    except:
        print('json dumps failed on %s' % gradesjson)
        exit(1)
    #print('dumping %s' % str(jsondumpsoutput))
    gradesjsonoutput.write(jsondumpsoutput)
    gradesjsonoutput.write('\n')
    gradesjsonoutput.close()

    if do_unique:
        # Output <labname>.unique.json
        uniquejsonname = os.path.join(MYHOME, "%s.unique.json" % lab_id_name)
        uniquejsonoutput = open(uniquejsonname, "w")
        try:
            jsondumpsoutput = json.dumps(uniquejson, indent=4)
        except:
            print('json dumps failed on %s' % uniquejson)
            exit(1)
        #print('dumping %s' % str(jsondumpsoutput))
        uniquejsonoutput.write(jsondumpsoutput)
        uniquejsonoutput.write('\n')
        uniquejsonoutput.close()

    # Output <labname>.grades.txt
    gradestxtname = os.path.join(MYHOME, "%s.grades.txt" % lab_id_name)
    GenReport.CreateReport(gradesjsonname, gradestxtname, check_watermark)
    if do_unique:
        GenReport.UniqueReport(uniquejsonname, gradestxtname)

    # Inform user where the 'grades.txt' are created
    print "Grades are stored in '%s'" % gradestxtname
    return 0

if __name__ == '__main__':
    sys.exit(main())


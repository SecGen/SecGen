#!/usr/bin/env python
import os
import sys
import time
import datetime
import zipfile
import shutil
import glob
from io import BytesIO
instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
# Append Student CWD to sys.path
sys.path.append(student_cwd+"/bin")
import ParseLabtainerConfig
import labutils
import LabtainerLogging
''' Report on students submission of reports '''
def reportSum(zip_fname, xfer, expect_lab): 
    lab_xfer = os.path.join(xfer, expect_lab)
    reports_dir = os.path.join(lab_xfer, 'reports')
    sum_report = os.path.join(lab_xfer, 'missing_submits.txt')
    sum_fh = open(sum_report, 'w')
    ziplist = glob.glob(lab_xfer+'/*.zip')
    labs_dir = os.path.abspath('../../labs')
    lab_doc_dir = os.path.join(labs_dir, expect_lab, 'docs')
    orig_doc = glob.glob(lab_doc_dir+'/*emplate*docx')
    orig_size = None
    if len(orig_doc) > 0:
        orig_size = os.path.getsize(orig_doc[0])
   
    ''' get student list '''
    student_list = []
    with zipfile.ZipFile(zip_fname) as zip_file:
        for member_info in zip_file.infolist():
            member = member_info.filename
            parts = member.split('/')
            student = parts[1].strip()
            if student not in student_list:
                student_list.append(student)
    sum_fh.write('%-50s %-10s %-10s\n' % ('STUDENT', 'ZIP',  'REPORT'))
    sum_fh.write('%-50s %-10s %-10s\n' % ('=======', '===',  '======'))
    for student in student_list:
        gotreport = False
        email_pref = student[student.find("(")+1:student.find(")")]
        sdir = os.path.join(reports_dir, student)
        try:
            os.makedirs(sdir)
        except OSError:
            pass
        if os.path.isdir(sdir):
            rfiles = os.listdir(sdir)
            for report in rfiles:
                rpath = os.path.join(sdir, report)
                rsize = os.path.getsize(rpath)
                if orig_size is None or rsize != orig_size:
                    gotreport = True
        else:
            print("NO RESULTS FOR %s" % student)

        gotzip = False
        #print('email %s' % email_pref)
        for zfile in ziplist:
            bname = os.path.basename(zfile)
            if bname.startswith(email_pref):
                gotzip = True
        zip_line = ' '
        if not gotzip:
            zip_line = 'MISSING'       
        rep_line = ' '
        if not gotreport:
            rep_line = 'MISSING' 
        sum_fh.write('%-50s %-10s %-10s\n' % (student.strip(), zip_line, rep_line))
    sum_fh.close()

'''
Extract individual zip files from a saki bulk download
'''

def extract(zip_fname, xfer, expect_lab):
    ''' zip_fname is assumed a saki bulk zip file  of all student attachments for this assignment'''
    results_dir = os.path.join(xfer, expect_lab, 'reports')
    try:
        os.makedirs(results_dir)
    except OSError:
        pass
    count = 0
    unexpected = 0
    with zipfile.ZipFile(zip_fname) as zip_file:
        for member_info in zip_file.infolist():
            member = member_info.filename
            parts = member.split('/')
            student = parts[1]
            #print('STUDENT %s fname %s' % (student, member))
            date_time = time.mktime(member_info.date_time + (0, 0, -1))
            filename = os.path.basename(member)
            if filename.endswith('.zip'):
                parts = filename.split('.')
                lab = parts[-2]
                if lab == expect_lab:
                    count += 1
                else:
                    unexpected += 1
                lab_xfer = os.path.join(xfer, lab)
                # skip directories
                if not filename:
                    continue

                # copy file (taken from zipfile's extract) into xfer for lab
                source = zip_file.open(member)
                #print("filename <%s>  lab_xfer is %s" % (filename, lab_xfer))
                if not os.path.isdir(lab_xfer):
                    print('ERROR ******: no such xfer directory. student: %s, file %s' % (student, member))
                    continue 
                target = file(os.path.join(lab_xfer, filename), "wb")
                with source, target:
                    shutil.copyfileobj(source, target)

                # copy reports
                target_dir = os.path.join(results_dir, student)
                try:
                    os.makedirs(target_dir)
                except OSError:
                    pass
                zip_file_data = BytesIO(zip_file.read(member))
                with zipfile.ZipFile(zip_file_data) as zip_zips:
                   for zi in zip_zips.namelist():
                       if zi == 'docs.zip':
                           docs_zip_data = BytesIO(zip_zips.read(zi))
                           with zipfile.ZipFile(docs_zip_data) as zip_docs:
                               for zdoc_info in zip_docs.infolist():
                                   zdoc = zdoc_info.filename     
                                   doc_date_time = time.mktime(zdoc_info.date_time + (0, 0, -1))
                                   #print('student %s look for report in %s' % (student, zdoc))
                                   fname, ext = os.path.splitext(zdoc)
                                   if (ext == '.docx' or ext == '.odt' or ext == '.xlsx') and (fname+'.pdf' not in zip_docs.namelist()):
                                       if not os.path.isfile(os.path.join(target_dir, zdoc)):
                                           source = zip_docs.open(zdoc)
                                           if os.path.isdir(target_dir):
                                               target = file(os.path.join(target_dir, os.path.basename(zdoc)), "wb")
                                               #zip_docs.extract(zdoc, target_dir)
                                               shutil.copyfileobj(source, target)
                                               #print('copied report %s to %s' % (zdoc, target_dir))
                                               os.utime(os.path.join(target_dir, zdoc), (doc_date_time, doc_date_time))
                                           else:
                                               print('no dir at %s' % target_dir)
                                       else:
                                           print('found doc at %s, do not overwrite' % os.path.join(target_dir, zdoc))

            else:
                fname, ext = os.path.splitext(filename)
                #print('fname is %s' % fname)
                if (ext == '.docx' or ext == '.odt' or ext == '.xlsx'): 
                    source = zip_file.open(member)
                    parts = member.split('/')
                    student = parts[1]
                    target_dir = os.path.join(results_dir, student)
                    #print('target_dir is %s' % target_dir)
                    try:
                        os.makedirs(target_dir)
                    except OSError:
                        pass
                    #zip_file.extract(member, target_dir)
                    target = file(os.path.join(target_dir, filename), "wb")
      
                    #print('copied %s to %s' % (source, target))
                    shutil.copyfileobj(source, target)
                    os.utime(os.path.join(target_dir, filename), (date_time, date_time))
    if count > 0:
        print('Extracted %d student zip files' % count)
    if unexpected > 0:
        print('Extracted %d for other labs' % unexpected)
    reportSum(zip_fname, xfer, expect_lab)
   
               

def checkBulkSaki(lab):
    labtainer_config_dir = '../../config/labtainer.config'
    labutils.logger = LabtainerLogging.LabtainerLogging("saki.log", 'none', labtainer_config_dir)
    labtainer_config = ParseLabtainerConfig.ParseLabtainerConfig(labtainer_config_dir, labutils.logger)
    home = os.getenv('HOME')
    xfer = os.path.join(home, labtainer_config.host_home_xfer)
    bulk_path = os.path.join(xfer, lab, 'bulk_download.zip') 
    if os.path.isfile(bulk_path):
        extract(bulk_path, xfer, lab)
    else: 
        lxfer = os.path.join(xfer, lab)
        zfiles = glob.glob(lxfer+'/*.zip')
        for z in zfiles:
            f = os.path.basename(z).rsplit('.',1)[0]
            if '_' in f:
                ts = f.split('_')[1]
                try:
                    v = time.mktime(datetime.datetime.strptime(ts,'%Y%m%d%H%M%S').timetuple())
                    print('Assuming Sakai bulk download: %s' % z)
                    extract(z, xfer, lab)
                except:
                    pass


if __name__ == '__main__':
    lab = sys.argv[1]
    checkBulkSaki(lab)

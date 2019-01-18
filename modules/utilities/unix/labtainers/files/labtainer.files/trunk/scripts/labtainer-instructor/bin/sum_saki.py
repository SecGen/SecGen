#!/usr/bin/env python
import sys
import os
import glob
import zipfile
import shutil
from io import BytesIO
path = '/tmp/mft/Lab 1 - Syslog'
results = '/tmp/mft/syslog-results'
student_dirs = os.listdir(path)
print('%-40s  %10s  %10s' % ('student', 'report', 'zip'))
for s in student_dirs:
   #spath = os.path.join(path, s, 'Submission attachment(s):')
   sresults = os.path.join(results, s)
   try:
       os.makedirs(sresults)
   except:
       pass
   spath = os.path.join(path, s)
   sub_list = os.listdir(spath)
   doc_status = 'NONE'
   zip_status = 'NONE'
   zip_file_data = None
   doc_list = [] 
   for sub in sub_list:
       if sub.startswith('Submiss'):
           sub_dir = os.path.join(spath, sub)
           student_subs = os.listdir(sub_dir)
           for ssub in student_subs:
               if ssub.endswith('docx'):
                   doc_status = 'docx'
                   doc_list.append(os.path.join(sub_dir, ssub))
               elif ssub.endswith('odt'):
                   doc_status = 'odt'
                   doc_list.append(os.path.join(sub_dir, ssub))
               elif ssub.endswith('.zip'):
                   zip_status = 'yes'
                   zippath = os.path.join(sub_dir, ssub)
                   zipoutput = zipfile.ZipFile(zippath, "r")
                   if doc_status == 'NONE':
                       for zi in zipoutput.infolist():
                           zname = zi.filename
                           if zname == 'docs.zip':
                               doc_status = 'docs.zip'
                               zip_file_data = BytesIO(zipoutput.read(zname))
                                       
   #print('doc_status is %s' % doc_status)
   if doc_status == 'NONE' and zip_file_data is not None:
       with zipfile.ZipFile(zip_file_data) as zip_docs:
          for zi in zip_docs.namelist():
              fname, ext = os.path.splitext(zi)
              if (ext == '.docx' or ext == '.odg') and (fname+'.pdf' not in zip_docs.namelist()):
                  zip_docs.extract(zi, sresults)
   else:
       for doc in doc_list:
           shutil.copyfile(doc, os.path.join(sresults, os.path.basename(doc)))
           
   print('%-40s  %10s  %10s' % (s, doc_status, zip_status))
                
        

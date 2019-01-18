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
import sys
import os
import shutil
'''
Look at _tar directories for the given labs/[lab]/[image] and
create or update tar files to reflect recent changes.  Uses
an 'external-manifest' file to identify tars from other labs
that should be part of this one.
'''
external = 'external-manifest'
tmp_loc = '/tmp/check_tar'
def expandManifest(full, tar_name):
    ''' 
    extract files from a tar named in an external manifest file
    into a staging directory at tmp_loc
    '''
    #print('expand for %s' % full)
    mf = os.path.join(full, external)
    labdir = os.path.dirname(os.path.dirname(os.path.dirname(full)))
    #print('labdir is %s' % labdir)
    with open(mf) as fh:
        for line in fh:
            lab, image = line.strip().split(':')
            ref_tar = os.path.join(labdir, lab, image, os.path.basename(full), tar_name)
            #print('external ref is %s' % ref_tar)
            cmd = 'tar xf %s -C %s' % (ref_tar, tmp_loc)
            os.system(cmd)

def newest_referenced_tar(full, tar_name):
    '''
    return a path to the most recent tar file named in an external
    manifest.
    '''
    retval = None
    recent = 0
    labdir = os.path.dirname(os.path.dirname(os.path.dirname(full)))
    mf = os.path.join(full, external)
    with open(mf) as fh:
        for line in fh:
            lab, image = line.strip().split(':')
            ref_tar = os.path.join(labdir, lab, image, os.path.basename(full), tar_name)
            if not os.path.isfile(ref_tar):
                print('Tar file named in manifest not found: %s component %s' % (ref_tar, full))
                exit(1)
            tar_time = os.stat(ref_tar).st_mtime
            if tar_time > recent:
                retval = ref_tar
                recent = tar_time
    return retval
    
def newest_file_in_tree(rootfolder):
    return max(
        (os.path.join(dirname, filename)
        for dirname, dirnames, filenames in os.walk(rootfolder)
        for filename in filenames),
        key=lambda fn: os.stat(fn).st_mtime)


def copydir(source, dest):
    """Copy a directory structure overwriting existing files"""
    for root, dirs, files in os.walk(source):
        if not os.path.isdir(root):
            os.makedirs(root)

        for file in files:
            rel_path = root.replace(source, '').lstrip(os.sep)
            dest_path = os.path.join(dest, rel_path)

            if not os.path.isdir(dest_path):
                os.makedirs(dest_path)
            shutil.copyfile(os.path.join(root, file), os.path.join(dest_path, file))

def CheckTars(container_dir, image_name, logger):
    here = os.getcwd()
    if container_dir.endswith('/'):
        container_dir = container_dir[:-1]
    tar_list = os.listdir(container_dir)
    manifest_name = '%s-home_tar.list' % image_name
    lab_dir = os.path.dirname(container_dir)
    logger.DEBUG('container_dir is %s' % container_dir)
    manifest = os.path.join(lab_dir, 'config', manifest_name)
    for f in tar_list:
        full = os.path.join(container_dir, f)
        if os.path.isdir(full) and f.endswith('_tar'):
            try:
                logger.DEBUG('remove tree at %s' % tmp_loc)
                shutil.rmtree(tmp_loc)
            except:
                pass
            os.mkdir(tmp_loc)
            os.chdir(full)
            tmp_name = f[:-4]
            tar_name = tmp_name+'.tar'
            logger.DEBUG('check for %s' % tar_name)
            if not os.path.isfile(tar_name):
                ''' no tar, make one '''
                logger.DEBUG('no tar %s, make one' % tar_name)
                f_list = os.listdir('./')
                if len(f_list) == 0:
                    #print('no files, make empty')
                    ''' no files at all, create empty archive '''
                    cmd = 'tar cvf %s --files-from /dev/null' % tar_name
                    os.system(cmd)
                    logger.DEBUG('did %s' % cmd)
                else:
                    if external in f_list:
                        ''' external manifest, expand that '''
                        logger.DEBUG('expand manifest at %s' % full)
                        expandManifest(full, tar_name)
                    for cfile in f_list:
                        logger.DEBUG('cfile is %s' % cfile)
                        if cfile != external:
                            if os.path.isdir(cfile):
                                copydir(cfile, os.path.join(tmp_loc, cfile))
                            else:
                                shutil.copytree(cfile, os.path.join(tmp_loc, cfile))
                    os.chdir(tmp_loc)
                    full_tar = os.path.join(full, tar_name)
                    if f == 'home_tar':
                        cmd = 'tar czf %s `ls -A -1` > %s' % (full_tar, manifest)
                    else:
                        cmd = 'tar czf %s `ls -A -1`' % (full_tar)
                    os.system(cmd)
                    #print('did %s' % cmd)
            else:
                ''' is a tar file, should it be updated? '''
                os.chdir(full)
                newest = newest_file_in_tree('./') 
                logger.DEBUG('newest is %s' % newest)
                referenced_tar_newer = False 
                if os.path.isfile(external): 
                    latest_ref = newest_referenced_tar(full, tar_name)
                    logger.DEBUG('has manifest, is referenced file (%s) newer than local tar?' % latest_ref)
                    if os.stat(latest_ref).st_mtime > os.stat(tar_name).st_mtime:
                        referenced_tar_newer = True
                    
                if referenced_tar_newer or not newest.endswith(tar_name):
                    os.remove(tar_name)
                    flist = os.listdir('./')
                    for f in flist:
                        if f == external:
                            continue
                        shutil.copytree(f , os.path.join(tmp_loc,f))
                    ''' something is newer than the tar, need to update tar '''
                    if os.path.isfile(os.path.join('./', external)):
                        expandManifest(full, tar_name)
                    os.chdir(tmp_loc)
                    full_tar = os.path.join(full, tar_name)
                    if f == 'home_tar':
                        cmd = 'tar czf %s `ls -A -1` > %s' % (full_tar, manifest)
                    else:
                        cmd = 'tar czf %s `ls -A -1`' % (full_tar)
                    os.system(cmd)
                    logger.DEBUG(cmd)
                    #print('did %s' % cmd)
                else:
                    ''' tar file is the most recent.  ensure we have a manifest '''
                    if f == 'home_tar' and not os.path.isfile(manifest):
                        os.chdir(full)
                        cmd =  'tar tf %s > %s' % (tar_name, manifest) 
                        os.system(cmd)
                        logger.DEBUG(cmd)
        os.chdir(here)
    noskip_file = os.path.join(container_dir,'_bin', 'noskip')
    #print('look for %s' % noskip_file)
    if os.path.isfile(noskip_file):
        ''' files from home_tar that we want to collect from student -- normally home_tar files are not collected '''
        #print('is a skip')
        with open(noskip_file) as fh:
            for line in fh:
                line = line.strip()
                #print('check for %s' % line)
                if len(line) > 0:
                    cmd = 'sed -i /%s$/d %s' % (line, manifest)
                    os.system(cmd)
                        
                     
def __main__():                    
    container_dir = sys.argv[1]
    image_name = sys.argv[2]
    CheckTars(container_dir, image_name)

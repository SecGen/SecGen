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

# ParameterParser.py
# Description: * Read parameter.config
#              * Parse stdin and stdout files based on parameter.config

import glob
import md5
import os
import random
import sys
import ParameterizeLogging

class ParameterParser():
    def __init__(self, container_name, container_user, lab_instance_seed, logger=None, lab=None):    
        ''' NOTE: container_name is none if running on Linux host vice a container, e.g., for start.config '''
        self.randreplacelist = {}
        self.unique_values = {}
        self.hashcreatelist = {}
        self.hashreplacelist = {}
        self.clonereplacelist = {}
        self.paramlist = {}
        self.container_user = container_user
        self.container_name = container_name
        self.lab_instance_seed = lab_instance_seed
        self.lab = lab
        if logger is None:
            self.logger = ParameterizeLogging.ParameterizeLogging("/tmp/parameterize.log")
        else:
            self.logger = logger
        self.logger.DEBUG('start parsing parameters')
    
    def WatermarkCreate(self):
        watermarkcreatelist = {}
        the_watermark_string = "LABTAINER_WATERMARK1"
        # Create hash per the_watermark_string (note: there is only one watermark file for now)
        string_to_be_hashed = '%s:%s' % (self.lab_instance_seed, the_watermark_string)
        mymd5 = md5.new()
        mymd5.update(string_to_be_hashed)
        mymd5_hex_string = mymd5.hexdigest()
        #logger.DEBUG(mymd5_hex_string)
    
        # Assume only one watermark file with filename /home/<container_user>/.local/.watermark
        myfilename = '/home/%s/.local/.watermark' % self.container_user
    
        # If file does not exist, create an empty file
        if not os.path.exists(myfilename):
            outfile = open(myfilename, 'w')
            outfile.write('')
            outfile.close()
    
        # Only one watermark file for now
        watermarkcreatelist[myfilename] = []
        watermarkcreatelist[myfilename].append('%s' % mymd5_hex_string)
    
        #logger.DEBUG("Perform_WATERMARK_CREATE")
        for (listfilename, createlist) in watermarkcreatelist.items():
            filename = listfilename
            #logger.DEBUG("Current Filename is %s" % filename)
            #print "Watermark Create list is "
            #print createlist
            # open the file - write
            outfile = open(filename, 'w')
            for the_string in createlist:
                outfile.write('%s\n' % the_string)
            outfile.close()
    
    def CheckRandReplaceEntry(self, param_id, each_value, unique=False):
        # RAND_REPLACE : <filename> : <token> : <LowerBound> : <UpperBound>
        #print "Checking RAND_REPLACE entry"
        entryline = each_value.split(': ')
        #print entryline
        numentry = len(entryline)
        if numentry != 4:
            self.logger.ERROR("RAND_REPLACE (%s) improper format" % each_value)
            #logger.ERROR("RAND_REPLACE : <filename> : <token> : <LowerBound> : <UpperBound>")
            sys.exit(1)
        myfilename_field = entryline[0].strip()
        token = entryline[1].strip()
        #print "filename is (%s)" % myfilename
        #print "token is (%s)" % token
    
        # Converts lowerbound and upperbound as integer - and pass to
        # random.randint(a,b)
        # Starts with assuming will use integer (instead of hexadecimal)
        use_integer = True
        lowerboundstr = entryline[2].strip()
        if lowerboundstr.startswith('0x'):
            use_integer = False
            lowerbound_int = int(lowerboundstr, 16)
        else:
            lowerbound_int = int(lowerboundstr, 10)
        upperboundstr = entryline[3].strip()
        if upperboundstr.startswith('0x'):
            if use_integer == True:
                # Inconsistent format of lowerbound (integer format)
                # vs upperbound (hexadecimal format)
                self.logger.ERROR("RAND_REPLACE (%s) inconsistent lowerbound/upperbound format" % each_value)
                #self.logger.ERROR("RAND_REPLACE : <filename> : <token> : <LowerBound> : <UpperBound>")
                sys.exit(1)
            use_integer = False
            upperbound_int = int(upperboundstr, 16)
        else:
            if use_integer == False:
                # Inconsistent format of lowerbound (hexadecimal format)
                # vs upperbound (integer format)
                self.logger.ERROR("RAND_REPLACE (%s) inconsistent lowerbound/upperbound format" % each_value)
                #self.logger.ERROR("RAND_REPLACE : <filename> : <token> : <LowerBound> : <UpperBound>")
                sys.exit(1)
            upperbound_int = int(upperboundstr, 10)
        #print "lowerbound is (%d)" % lowerbound_int
        #print "upperbound is (%d)" % upperbound_int
        if lowerbound_int > upperbound_int:
            self.logger.ERROR("RAND_REPLACE (%s) lowerbound greater than upperbound" % each_value)
            sys.exit(1)
        if unique:
            key = '%d-%d' % (lowerbound_int, upperbound_int)
            if key not in self.unique_values:
                self.unique_values[key] = []
            num_possible = upperbound_int - lowerbound_int + 1
            if len(self.unique_values) >= num_possible:
                self.logger.ERROR("unqiue values for %s consumed" % key)
                sys.exit(1)
            while True:
                random_int = random.randint(lowerbound_int, upperbound_int)
                if random_int not in self.unique_values[key]:
                    self.unique_values[key].append(random_int)
                    break
        else:
            random_int = random.randint(lowerbound_int, upperbound_int)
        #print "random value is (%d)" % random_int
        if use_integer:
            random_str = '%s' % int(random_int)
        else:
            random_str = '%s' % hex(random_int)
    
        myfilename_list = myfilename_field.split(';')
        for myfilename in myfilename_list:
            # Check to see if ':' in myfilename
            if ':' in myfilename:
                # myfilename has the container_name also
                tempcontainer_name, myactualfilename = myfilename.split(':')
                # Assume filename is relative to /home/<container_user>
                if not myactualfilename.startswith('/'):
                    user_home_dir = '/home/%s' % self.container_user
                    myfullactualfilename = os.path.join(user_home_dir, myactualfilename)
                else:
                    myfullactualfilename = myactualfilename
                myfilename = '%s:%s' % (tempcontainer_name, myfullactualfilename)
            else:
                # myfilename does not have the containername
                # Assume filename is relative to /home/<container_user>
                if not myfilename.startswith('/') and myfilename != 'start.config':
                    user_home_dir = '/home/%s' % self.container_user
                    myfullfilename = os.path.join(user_home_dir, myfilename)
                else:
                    myfullfilename = myfilename
                myfilename = myfullfilename
        
            if myfilename in self.randreplacelist:
                self.randreplacelist[myfilename].append('%s:%s' % (token, random_str))
            else:
                self.randreplacelist[myfilename] = []
                self.randreplacelist[myfilename].append('%s:%s' % (token, random_str))
        self.paramlist[param_id] = random_str
    
    
    def CheckHashCreateEntry(self, param_id, each_value):
        # HASH_CREATE : <filename> : <string>
        #print "Checking HASH_CREATE entry"
        entryline = each_value.split(': ')
        #print entryline
        numentry = len(entryline)
        if numentry != 2 and numentry != 3:
            self.logger.ERROR("HASH_CREATE : <filename> : <string> [: length]")
            sys.exit(1)
        myfilename_field = entryline[0].strip()
        the_string = entryline[1].strip()
        strlen = 32
        if numentry == 3:
            try:
                strlen = int(entryline[2].strip())
            except:      
                self.logger.ERROR("HASH_CREATE (%s) improper format" % each_value)
                self.logger.ERROR("expected int for length, got %s" % entryline[2])
                sys.exit(1)
    
        # Create hash per the_string
        string_to_be_hashed = '%s:%s' % (self.lab_instance_seed, the_string)
        mymd5 = md5.new()
        mymd5.update(string_to_be_hashed)
        mymd5_hex_string = mymd5.hexdigest()[:strlen]
        #print mymd5_hex_string
        #print "filename is (%s)" % myfilename_field
        #print "the_string is (%s)" % the_string
        #print "mymd5_hex_string is (%s)" % mymd5_hex_string
        # If container_user == "" then it must be instructor container
        # then skip actual creation of hash file
        if self.container_user != "":
            # Check to see if ':' in myfilename
            myfilename_list = myfilename_field.split(';')
            for myfilename in myfilename_list:
                if ':' in myfilename:
                    # myfilename has the container_name also
                    tempcontainer_name, myactualfilename = myfilename.split(':')
                    # Assume filename is relative to /home/<container_user>
                    if not myactualfilename.startswith('/'):
                        user_home_dir = '/home/%s' % self.container_user
                        myfullactualfilename = os.path.join(user_home_dir, myactualfilename)
                    else:
                        myfullactualfilename = myactualfilename
                    myfilename = '%s:%s' % (tempcontainer_name, myfullactualfilename)
                else:
                    # myfilename does not have the containername
                    # Assume filename is relative to /home/<container_user>
                    if not myfilename.startswith('/') and myfilename != 'start.config':
                        user_home_dir = '/home/%s' % self.container_user
                        myfullfilename = os.path.join(user_home_dir, myfilename)
                    else:
                        myfullfilename = myfilename
                    myfilename = myfullfilename
    
                # If file does not exist, create an empty file
                if not os.path.exists(myfilename):
                    outfile = open(myfilename, 'w')
                    outfile.write('')
                    outfile.close()
    
                if myfilename in self.hashcreatelist:
                    self.hashcreatelist[myfilename].append('%s' % mymd5_hex_string)
                else:
                    self.hashcreatelist[myfilename] = []
                    self.hashcreatelist[myfilename].append('%s' % mymd5_hex_string)
    
        # Update paramlist regardless
        self.paramlist[param_id] = mymd5_hex_string
    
    def CheckHashReplaceEntry(self, param_id, each_value):
        # HASH_REPLACE : <filename> : <token> : <string>
        #print "Checking HASH_REPLACE entry"
        entryline = each_value.split(': ')
        #print entryline
        numentry = len(entryline)
        if numentry != 3 and numentry != 4:
            self.logger.ERROR("HASH_REPLACE (%s) improper format" % each_value)
            #self.logger.ERROR("HASH_REPLACE : <filename> : <symbol> : <string> [: length]")
            sys.exit(1)
        strlen = 32
        if numentry == 4:
            try:
                strlen = int(entryline[3].strip())
            except:      
                self.logger.ERROR("HASH_REPLACE (%s) improper format" % each_value)
                self.logger.ERROR("expected int for length, got %s" % entryline[3])
                sys.exit(1)
        myfilename_field = entryline[0].strip()
        token = entryline[1].strip()
        the_string = entryline[2].strip()
        # Create hash per the_string
        string_to_be_hashed = '%s:%s' % (self.lab_instance_seed, the_string)
        mymd5 = md5.new()
        mymd5.update(string_to_be_hashed)
        mymd5_hex_string = mymd5.hexdigest()[:strlen]
        #print "filename is (%s)" % myfilename_field
        #print "token is (%s)" % token
        #print "the_string is (%s)" % the_string
    
        # Check to see if ':' in myfilename
        myfilename_list = myfilename_field.split(';')
        for myfilename in myfilename_list:
            if ':' in myfilename:
                # myfilename has the container_name also
                tempcontainer_name, myactualfilename = myfilename.split(':')
                # Assume filename is relative to /home/<container_user>
                if not myactualfilename.startswith('/'):
                    user_home_dir = '/home/%s' % self.container_user
                    myfullactualfilename = os.path.join(user_home_dir, myactualfilename)
                else:
                    myfullactualfilename = myactualfilename
                myfilename = '%s:%s' % (tempcontainer_name, myfullactualfilename)
            else:
                # myfilename does not have the containername
                # Assume filename is relative to /home/<container_user>
                if not myfilename.startswith('/') and myfilename != 'start.config':
    
                    user_home_dir = '/home/%s' % self.container_user
                    myfullfilename = os.path.join(user_home_dir, myfilename)
                else:
                    myfullfilename = myfilename
                myfilename = myfullfilename
    
            if myfilename in self.hashreplacelist:
                self.hashreplacelist[myfilename].append('%s:%s' % (token, mymd5_hex_string))
            else:
                self.hashreplacelist[myfilename] = []
                self.hashreplacelist[myfilename].append('%s:%s' % (token, mymd5_hex_string))
        self.paramlist[param_id] = mymd5_hex_string
    
    def CheckCloneReplaceEntry(self, param_id, each_value):
        # HASH_REPLACE : <filename> : <token> : <string>
        #print "Checking HASH_REPLACE entry"
        if self.container_name is None:
            return
        entryline = each_value.split(': ')
        #print entryline
        numentry = len(entryline)
                
        myfilename_field = entryline[0].strip()
        token = entryline[1].strip()
        clone_num = '' 
        if '-' in self.container_name:
            dumb, clone_num = self.container_name.rsplit('-', 1)
        # Check to see if ':' in myfilename
        myfilename_list = myfilename_field.split(';')
        for myfilename in myfilename_list:
            if ':' in myfilename:
                # myfilename includes the container_name 
                tempcontainer_name, myactualfilename = myfilename.split(':')
                self.logger.DEBUG('tmpcontainer_name is %s fname %s' % (tempcontainer_name, myactualfilename))
                # Assume filename is relative to /home/<container_user>
                if not myactualfilename.startswith('/'):
                    user_home_dir = '/home/%s' % self.container_user
                    myfullactualfilename = os.path.join(user_home_dir, myactualfilename)
                else:
                    myfullactualfilename = myactualfilename
                if clone_num != '':
                    myfilename = '%s-%s:%s' % (tempcontainer_name, clone_num, myfullactualfilename)
                else:
                    myfilename = '%s:%s' % (tempcontainer_name, myfullactualfilename)
                self.logger.DEBUG('myfilename now %s' % myfilename)
            else:
                # myfilename does not have the containername
                # Assume filename is relative to /home/<container_user>
                if not myfilename.startswith('/') and myfilename != 'start.config':
    
                    user_home_dir = '/home/%s' % self.container_user
                    myfullfilename = os.path.join(user_home_dir, myfilename)
                else:
                    myfullfilename = myfilename
                myfilename = myfullfilename
    
            if myfilename not in self.clonereplacelist:
                self.clonereplacelist[myfilename] = []
            self.clonereplacelist[myfilename].append('%s:%s' % (token, clone_num))
        self.paramlist[param_id] = clone_num
    
    
    def ValidateParameterConfig(self, param_id, each_key, each_value):
        ''' build file/token replacment list for each type of replacement '''
        if each_key == "RAND_REPLACE":
            #print "RAND_REPLACE"
            self.CheckRandReplaceEntry(param_id, each_value)
        elif each_key == "RAND_REPLACE_UNIQUE":
            #print "RAND_REPLACE"
            self.CheckRandReplaceEntry(param_id, each_value, unique=True)
        elif each_key == "HASH_CREATE":
            #print "HASH_CREATE"
            self.CheckHashCreateEntry(param_id, each_value)
        elif each_key == "HASH_REPLACE":
            #print "HASH_REPLACE"
            self.CheckHashReplaceEntry(param_id, each_value)
        elif each_key == "CLONE_REPLACE":
            #print "CLONE"
            self.CheckCloneReplaceEntry(param_id, each_value)
        else:
            self.logger.ERROR("ParseParameter.py, ValidateParameterConfig, Invalid operator %s" % each_key)
            sys.exit(1)
        return 0
    
    # Perform RAND_REPLACE
    def Perform_RAND_REPLACE(self):
        # At this point randreplacelist should have been populated
        # and files have been confirmed to exist
    
        #print "Perform_RAND_REPLACE"
        for (listfilename, replacelist) in self.randreplacelist.items():
            if self.container_name is None:
                ''' running on linux host before container creation '''
                if listfilename != 'start.config':
                    #print('listfile is <%s>' % listfilename)
                    self.logger.DEBUG('running on host, not start.config')
                    continue
                else:
                    filename = os.path.join('./.tmp', self.lab, 'start.config')
            elif ':' in listfilename:
                # listfilename has the containername also
                if self.container_name != "" and listfilename.startswith(self.container_name+':'):
                    tmp_container_name, filename = listfilename.split(':')
                else:
                    # Not for this container
                    continue
            elif listfilename == 'start.config':
                continue
            else:
                filename = listfilename
            #print "Current Filename is %s" % filename
            if not os.path.exists(filename):
                self.logger.ERROR("Perform_RAND_REPLACE: File %s does not exist" % filename)
                sys.exit(1)
            #else:
            #    print "File (%s) exist\n" % filename
            #print "Replace list is "
            #print replacelist
            content = None
            # First open the file - read
            infile = open(filename, 'r') 
            content = infile.read()
            for replaceitem in replacelist:
                (oldtoken, newtoken) = replaceitem.split(':')
                content = content.replace(oldtoken, newtoken)
            infile.close()
            # Re-open file with write
            outfile = open(filename, 'w') 
            outfile.write(content)
            outfile.close()

            
    
    # Perform HASH_CREATE
    def Perform_HASH_CREATE(self):
        # At this point hashcreatelist should have been populated
        # and files have been confirmed to exist or created
    
        #print "Perform_HASH_CREATE"
        for (listfilename, createlist) in self.hashcreatelist.items():
            if self.container_name is None:
                ''' running one linux host before container creation '''
                if listfilename != 'start.config':
                    #print('listfile is <%s>' % listfilename)
                    self.logger.DEBUG('running on host, not start.config')
                    continue
                else:
                    filename = '/tmp/start.config'
            elif ':' in listfilename:
                # listfilename has the containername also
                if self.container_name != "" and listfilename.startswith(self.container_name+':'):
                    tmp_container_name, filename = listfilename.split(':')
                else:
                    # Not for this container
                    continue
            else:
                filename = listfilename
            #print "Current Filename is %s" % filename
            #print "Hash Create list is "
            #print createlist
            # open the file - write
            outfile = open(filename, 'w')
            for the_string in createlist:
                outfile.write('%s\n' % the_string)
            outfile.close()
    
    # Perform HASH_REPLACE
    def Perform_HASH_REPLACE(self):
        # At this point hashreplacelist should have been populated
        # and files have been confirmed to exist
    
        #print "Perform_HASH_REPLACE"
        #print hashreplacelist
        for (listfilename, replacelist) in self.hashreplacelist.items():
            if self.container_name is None:
                ''' running one linux host before container creationt '''
                if listfilename != 'start.config':
                    #print('listfile is <%s>' % listfilename)
                    self.logger.DEBUG('running on host, not start.config')
                    continue
                else:
                    filename = '/tmp/start.config'
            elif ':' in listfilename:
                # listfilename has the containername also
                #print "listfilename is (%s)" % listfilename
                #print "container_name is (%s)" % container_name
                if self.container_name != "" and listfilename.startswith(self.container_name+':'):
                    #print "Yes it startswith"
                    tmp_container_name, filename = listfilename.split(':')
                else:
                    #print "No it does not startswith"
                    # Not for this container
                    continue
            else:
                #print "Does not have :"
                filename = listfilename
            #print "Current Filename is %s" % filename
            if not os.path.exists(filename):
                self.logger.ERROR("Perform_HASH_REPLACE: File %s does not exist" % filename)
                sys.exit(1)
            #else:
            #    print "File (%s) exist\n" % filename
            #print "Replace list is "
            #print replacelist
            content = None
       
            infile = open(filename, 'r')
            content = infile.read() 
            for replaceitem in replacelist:
                (oldtoken, newtoken) = replaceitem.split(':')
                content = content.replace(oldtoken, newtoken)
            infile.close()
            # Re-open file with write
            outfile = open(filename, 'w') 
            outfile.write(content)
            outfile.close()
    
    def Perform_CLONE_REPLACE(self):
        #print clonereplacelist
        if self.container_name is None:
            ''' running on linux host prior to container creation '''
            return
        for (listfilename, replacelist) in self.clonereplacelist.items():
            if ':' in listfilename:
                # listfilename includes the containername 
                #print "listfilename is (%s)" % listfilename
                #print "container_name is (%s)" % container_name
                self.logger.DEBUG('self.container_name is <%s>, listfilename is <%s>' % (self.container_name, listfilename))
                if self.container_name != "" and listfilename.startswith(self.container_name+':'):
                    #print "Yes it startswith"
                    tmp_container_name, filename = listfilename.split(':')
                else:
                    #print "No it does not startswith"
                    # Not for this container
                    self.logger.DEBUG('does not startwith')
                    continue
            else:
                #print "Does not have :"
                filename = listfilename
            self.logger.DEBUG("Current Filename is %s" % filename)
            if not os.path.exists(filename):
                self.logger.ERROR("Perform_CLONE_REPLACE: File %s does not exist" % filename)
                sys.exit(1)
            #else:
            #    print "File (%s) exist\n" % filename
            #print "Replace list is "
            #print replacelist
            content = None
       
            infile = open(filename, 'r')
            content = infile.read() 
            for replaceitem in replacelist:
                (oldtoken, newtoken) = replaceitem.split(':')
                content = content.replace(oldtoken, newtoken)
            infile.close()
            # Re-open file with write
            outfile = open(filename, 'w') 
            outfile.write(content)
            outfile.close()
    def DoReplace(self):
        # Do create Watermark here - instructor container does not call this
        #print "WATERMARK_CREATE"
        if self.container_name is not None:
            self.WatermarkCreate()
    
        # Perform RAND_REPLACE
        self.Perform_RAND_REPLACE()
        # Perform HASH_CREATE
        self.Perform_HASH_CREATE()
        # Perform HASH_REPLACE
        self.Perform_HASH_REPLACE()
        # Perform CLONE_REPLACE
        self.Perform_CLONE_REPLACE()
        self.logger.DEBUG('done parsing parameters')
    
    def ParseParameterConfig(self, configfilename):
        # Seed random with lab seed
        random.seed(self.lab_instance_seed)
        configfile = open(configfilename)
        configfilelines = configfile.readlines()
        configfile.close()
      
        for line in configfilelines:
            linestrip = line.rstrip()
            if linestrip:
                if not linestrip.startswith('#'):
                    #print "Current linestrip is (%s)" % linestrip
                    (param_id, each_key, each_value) = linestrip.split(': ', 2)
                    each_key = each_key.strip()
                    param_id = param_id.strip()
                    self.ValidateParameterConfig(param_id, each_key, each_value)
            #else:
            #    print "Skipping empty linestrip is (%s)" % linestrip
        return self.paramlist
    


# Usage: ParameterParser.py <lab_instance_seed> <container_name> [<config_file>]
# Arguments:
#     <container_user> - username of the container
#     <lab_instance_seed> - laboratory instance seed
#     <container_name> - name of the container"
#     [<config_file>] - optional configuration file
#                       if <config_file> not specified, it defaults to
#                       $HOME/.local/config/parameter.config
def main():
    container_name = None
    #print "Running ParameterParser.py"
    numargs = len(sys.argv)
    if not (numargs == 4 or numargs == 5):
        logger = ParameterizeLogging.ParameterizeLogging("/tmp/parameterize.log")
        logger.ERROR("ParameterParser.py <container_user> <lab_instance_seed> <container_name> [<config_file>]")
        sys.exit(1)

    container_user = sys.argv[1]
    lab_instance_seed = sys.argv[2]
    try:
        container_name = sys.argv[3].split('.')[1]
    except:
        logger = ParameterizeLogging.ParameterizeLogging("/tmp/parameterize.log")
        logger.ERROR('Could not parse container name from %s' % sys.argv[3])
        sys.exit(1)
        
    if numargs == 5:
        configfilename = sys.argv[4]
    else:
        configfilename = '/home/%s/.local/config/%s' % (container_user, "parameter.config")

    pp = ParameterParser(container_name, container_user, lab_instance_seed)
    ''' build the list of files and token replacements within those files '''
    pp.ParseParameterConfig(configfilename)
    ''' do the replacement '''
    pp.DoReplace()
    return 0

if __name__ == '__main__':
    sys.exit(main())


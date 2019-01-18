import os
import datetime
import json
'''
Keep a count of lab starts and redos.
'''
def getPath(start_path, labname):
    count_path = os.path.join(start_path, '.tmp', labname, 'count.json')
    if not os.path.isdir(os.path.dirname(count_path)):
       os.makedirs(os.path.dirname(count_path)) 
    return count_path

def addCount(start_path, labname, is_redo, logger):
    current_time_string = str(datetime.datetime.now())
    current_count = getLabCount(start_path, labname, logger)
    writeLabCount(start_path, labname, is_redo, current_count, current_time_string, logger)
    return len(current_count['start']+current_count['redo'])

def getLabCount(start_path, labname, logger):
    current_count = {}
    count_path = getPath(start_path, labname)
    if os.path.isfile(count_path):
        with open(count_path) as f:
            try:
                current_count = json.load(f)
            except:
                logger.WARNING('json load failed on %s, reset the counts.' % count_path)
                current_count['start'] = []
                current_count['redo'] = []
    else:
        current_count['start'] = []
        current_count['redo'] = []

    return current_count

def writeLabCount(start_path, labname, is_redo, current_count, current_time_string, logger):
    if is_redo:
        current_count['redo'].append(current_time_string)
    else:
        if 'normal' in current_count:
            current_count['normal'].append(current_time_string)
        else:
            try:
                current_count['start'].append(current_time_string)
            except:
                return
     
    count_path = getPath(start_path, labname)
    labname_file = open(count_path, "w")
    try:
        jsondumpsoutput = json.dumps(current_count, indent=4)
    except:
        logger.ERROR('json dumps failed on %s' % current_count)
        exit(1)
    labname_file.write(jsondumpsoutput)
    labname_file.write('\n')
    labname_file.close()

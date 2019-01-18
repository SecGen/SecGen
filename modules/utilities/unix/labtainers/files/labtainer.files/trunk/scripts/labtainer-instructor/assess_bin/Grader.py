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

# Grader.py
# Description: Grade the student lab work

import collections
try:
   from collections import OrderedDict
except:
   OrderedDict = dict
import filecmp
import json
import glob
import os
import sys
import subprocess
import ast
import string
import evalBoolean
import evalExpress
import InstructorLogging


default_timestamp = 'default-NONE'
def compare_time_during(goal1timestamp, goal2timestamp):
    goal1start, goal1end = goal1timestamp.split('-')
    goal2start, goal2end = goal2timestamp.split('-')
    #print "goal1start (%s) goal1end (%s)" % (goal1start, goal1end)
    #print "goal2start (%s) goal2end (%s)" % (goal2start, goal2end)
    if goal1end == '0':
        goal1end = goal1start
    if goal2end == '0':
        goal2end = goal2start
    if goal1start == 'default' or goal2start == 'default':
        return False
        #print "Can't compare 'default' timestamp!"
        #exit(1)
    if goal1end == 'NONE' or goal2end == 'NONE':
        return False
        #print "Can't compare 'NONE' timestamp!"
        #exit(1)
    if goal2start <= goal1start and goal1start <= goal2end:
        #print "goal2start (%s) <= goal1start (%s) <= goal2end (%s)" % (goal1start, goal2start, goal1end)
        return True
    else:
        #print "NOT - goal2start (%s) <= goal1start (%s) <= goal2end (%s)" % (goal1start, goal2start, goal1end)
        return False

def compare_time_before(goal1timestamp, goal2timestamp):
    goal1start, goal1end = goal1timestamp.split('-')
    goal2start, goal2end = goal2timestamp.split('-')
    if goal1start == 'default' or goal2start == 'default':
        print "Can't compare 'default' timestamp!"
        exit(1)
    if goal1start <= goal2start:
        #print "goal1start (%s) <= goal2start (%s)" % (goal1start, goal2start)
        return True
    else:
        return False

def evalTimeBefore(goals_tag1, goals_tag2):
    evalTimeBeforeResult = False
    for goal1timestamp, goal1value in goals_tag1.iteritems():
        #print "Goal1 timestamp is (%s) and value is (%s)" % (goal1timestamp, goal1value)
        # For each Goal1 value that is True
        if goal1value:
            for goal2timestamp, goal2value in goals_tag2.iteritems():
                #print "Goal2 timestamp is (%s) and value is (%s)" % (goal2timestamp, goal2value)
                # If there is Goal2 value that is True
                if goal2value:
                    #print "goal1ts (%s) goal2ts (%s)" % (goal1timestamp, goal2timestamp)
                    evalTimeBeforeResult = compare_time_before(goal1timestamp, goal2timestamp)
                    if evalTimeBeforeResult:
                        # if evalTimeBeforeResult is True - that means:
                        # (1) goals_tag1 is True and goals_tag2 is True
                        # (2) goal1start <= goal2start
                        break
        if evalTimeBeforeResult:
            break

    return evalTimeBeforeResult


def evalTimeDuring(goals_tag1, goals_tag2, logger):
    ''' Return a dictionary of booleans keyed with goals_tag2 time ranges for each goals_tag1
        that occured during the goals_tag2 time range. The boolean is true if at least
        one goals_tag1 value within the range is true''' 

    retval = {}
    ''' make sure dictionary contains entry for each goals_tag2 time range within which
        there exists at least one goals_tag1 time -- independent of the boolean values. '''
    for goal2timestamp, goal2value in goals_tag2.iteritems():
        #logger.DEBUG("Goal2 timestamp is (%s) and value is (%s)" % (goal2timestamp, goal2value))
        value_for_ts2 = None
        for goal1timestamp, goal1value in goals_tag1.iteritems():
            #logger.DEBUG("Goal1 timestamp is (%s) and value is (%s)" % (goal1timestamp, goal1value))
            eval_time_during_result = compare_time_during(goal1timestamp, goal2timestamp)
            if eval_time_during_result:
                #logger.DEBUG("is during Goal1 timestamp is (%s) and value is (%s)" % (goal1timestamp, goal1value))
                ''' at least one during event '''
                if value_for_ts2 is None:
                    value_for_ts2 = False
                value_for_ts2 = value_for_ts2 or (goal2value and goal1value)
        if value_for_ts2 is not None:
            retval[goal2timestamp] = value_for_ts2

    return retval

def evalTimeNotDuring(goals_tag1, goals_tag2, logger):
    ''' Return a dictionary of booleans keyed with all goals_tag2 time ranges.
        The boolean will be true if the goals_tag2 is true, and there exist
        not true values from goals_tag1 for that range.
        
    ''' 

    retval = {}
    ''' make sure dictionary contains entry for each goals_tag2 time range within which
        there exists at least one goals_tag1 time -- independent of the boolean values. '''
    for goal2timestamp, goal2value in goals_tag2.iteritems():
        #logger.DEBUG("Goal2 timestamp is (%s) and value is (%s)" % (goal2timestamp, goal2value))
        found_one = False
        ''' only can be true if goalvalue2 is true '''
        if goals_tag1 is not None and goal2value:
            for goal1timestamp, goal1value in goals_tag1.iteritems():
                #logger.DEBUG("Goal1 timestamp is (%s) and value is (%s)" % (goal1timestamp, goal1value))
                if goal1value:
                    eval_time_during_result = compare_time_during(goal1timestamp, goal2timestamp)
                    if eval_time_during_result:
                        #logger.DEBUG("is during Goal1 timestamp is (%s) and value is (%s)" % (goal1timestamp, goal1value))
                        found_one = True
        if found_one:
            retval[goal2timestamp] = False
        else:
            retval[goal2timestamp] = True

    return retval

class GoalTimes():
    def __init__(self):
        self.goals_id_ts = {}
        self.goals_ts_id = {}
        self.time_stamps = []
        self.singletons = {}

    def getGoalList(self):
        retval = []
        for goal_id in self.goals_id_ts:
            retval.append(goal_id) 
        return retval

    def hasGoal(self, goal_id):
        if goal_id in self.goals_id_ts:
            return True
        else:
            return False

    def getGoal(self, goal_id):
        if goal_id in self.goals_id_ts:
            return self.goals_id_ts[goal_id]
        else:
            return None

    def getGoalIdTimeStamp(self):
        return self.goals_id_ts
    def getGoalTimeStampId(self):
        return self.goals_ts_id

        
    def addGoal(self, goalid, goalts, goalvalue):
        '''
        Manage duplicate dictionaries with inverted key-value pairs.
        The 
        '''

        #print('in addGoal goalid %s goalts %s value %s' % (goalid, goalts, goalvalue))
        # Do goals_id_ts first
        if goalvalue == None:
            return
        if goalid not in self.goals_id_ts:
            self.goals_id_ts[goalid] = {}
            self.goals_id_ts[goalid][goalts] = goalvalue
        else:
            if goalts in self.goals_id_ts[goalid]:
                if goalts != default_timestamp:
                    # Already have that goal with that goalid and that timestamp
                    print("Grader.py add_goals_id_ts(1): duplicate goalid <%s> timestamp <%s> exit" % (goalid, goalts))
                    exit(1)
                else:
                    print("Grader.py add_goals_id_ts(1): duplicate goalid <%s> timestamp <%s>, return" % (goalid, goalts))
                    return
            else:
                self.goals_id_ts[goalid][goalts] = goalvalue
        # Do goals_ts_id next
        if goalts not in self.goals_ts_id:
            self.goals_ts_id[goalts] = {}
            self.goals_ts_id[goalts][goalid] = goalvalue
        else:
            if goalid in self.goals_ts_id[goalts]:
                # Already have that goal with that goalid and that timestamp
                print("Grader.py add_goals_id_ts(2): duplicate goalid timestamp!")
                exit(1)
            else:
                self.goals_ts_id[goalts][goalid] = goalvalue

def getJsonOutTS(outputjsonfile):
    jsonoutput = None
    with open(outputjsonfile, "r") as jsonfile:
        jsonoutput = json.load(jsonfile)
    if jsonoutput is None:
        return None
    for ts in jsonoutput:
        result_set = jsonoutput[ts]
        for key in result_set:
            old = result_set[key]
            new = ast.literal_eval(old)
            if new is not None:
                if type(new) is str:
                    new_filtered = filter(lambda x: x in string.printable, new)
                else:
                    new_filtered = new
            else:
                new_filtered = None
            result_set[key] = new_filtered 
        jsonoutput[ts] = result_set
        #print('is %s' % new)
    return jsonoutput

def getJsonOut(outputjsonfile):
    with open(outputjsonfile, "r") as jsonfile:
        jsonoutput = json.load(jsonfile)

    for key in jsonoutput:
        old = jsonoutput[key]
        try:
            new = ast.literal_eval(old)
        except:
            print('failed to do literal_eval on %s key was %s' % (old, key))
            exit(1)
        if new is not None:
            if type(new) is str:
                new_filtered = filter(lambda x: x in string.printable, new)
            else:
                new_filtered = new
        else:
            new_filtered = None
        jsonoutput[key] = new_filtered
        #print('is %s' % new)
    return jsonoutput


def compare_result_answer(current_result, current_answer, operator):
    found = False
    result_int = None
    # current_result may be an int, so turn to string first so
    # we can change it back!
    current_result = str(current_result)
    if "integer" in operator:
        try:
            if current_result.startswith('0x'):
                result_int = int(current_result, 16)
            else:
                result_int = int(current_result, 10)
        except ValueError:
            pass
            #print('Could not get integer from result <%s>' % current_result)
        try:
            if current_answer.startswith('0x'):
                answer_int = int(current_answer, 16)
            else:
                answer_int = int(current_answer, 10)
        except ValueError:
            pass
            #print('Could not get integer from answer <%s>' % current_answer)

    if operator == "string_equal":
        if current_result == current_answer:
            found = True
    elif operator == "string_diff":
        if current_result != current_answer:
            found = True
    elif operator == "string_start":
        if current_result.startswith(current_answer):
            found = True
    elif operator == "string_end":
        if current_result.endswith(current_answer):
            found = True
    elif operator == "string_contains":
        if current_answer in current_result:
            found = True
    elif operator == "integer_equal":
        if result_int == answer_int:
            found = True
    elif operator == "integer_greater":
        if result_int > answer_int:
            found = True
    elif operator == "integer_lessthan":
        if result_int < answer_int:
            found = True
    elif operator == "is_true":
        if current_result.lower() == 'true':
            found = True
    elif operator == "is_false":
        if current_result.lower() == 'false':
            found = True
    else:
        found = False

    return found

def processMatchLast(result_sets, eachgoal, goal_times):
    #print "Inside processMatchLast"
    found = False
    goalid = eachgoal['goalid']
    #print goalid
    jsonanswertag = eachgoal['answertag']
    #print jsonanswertag
    jsonresulttag = eachgoal['resulttag']
    (resulttagtarget, resulttag) = jsonresulttag.split('.')
    #print jsonresulttag
    # Handle special case 'answer=<string>'
    one_answer = False
    if '=' in jsonanswertag:
        (answertag, onlyanswer) = jsonanswertag.split('=')
        current_onlyanswer = onlyanswer.strip()
        # Change to one_answer = True
        one_answer = True
        #print "Current onlyanswer is (%s)" % current_onlyanswer
    else:
        # No more answer.config (parameter or parameter_ascii will become answer=<value> already)
        (use_target, answertagstring) = jsonanswertag.split('.')
        #print use_target
        #print answertagstring

    # MatchLast - Process only the last timestamp file
    # until match or not found
    results, ts = result_sets.getLatest()
    #print results
    if results == {}:
        # empty - skip
        return

    try:
        resulttagresult = results[resulttag]
    except:
        #print('processMatchLast: %s not found in file %s' % (resulttag, outputjsonfile))
        return
    #print resulttagresult
    try:
        timestampend = results['PROGRAM_ENDTIME']
    except:
        print('processMatchLast: PROGRAM_ENDTIME not found in file %s' % outputjsonfile)
        exit(1)
    fulltimestamp = '%s-%s' % (ts, timestampend)
    if one_answer:
        found = compare_result_answer(resulttagresult, current_onlyanswer, eachgoal['goaloperator'])
        if found:
            #print "resulttagresult is (%s) matches answer (%s)" % (resulttagresult, current_onlyanswer)
            goal_times.addGoal(goalid, fulltimestamp, True)
            return
    else:
        current_onlyanswer = results[answertagstring]
        #print "Correct onlyanswer is (%s)" % current_onlyanswer
        found = compare_result_answer(resulttagresult, current_onlyanswer, eachgoal['goaloperator'])
        if found:
            #print "resulttagresult is (%s) matches answer (%s)" % (resulttagresult, current_onlyanswer)
            goal_times.addGoal(goalid, fulltimestamp, True)
            return
 
    # All file processed - still not found
    if not found:
        #print "processMatchLast failed"
        goal_times.addGoal(goalid, fulltimestamp, False)

def processMatchAcross(result_sets, eachgoal, goal_times):
    '''  TBD, this seems wrong, should only be one answer for all timestamps? '''
    #print "Inside processMatchAcross"
    found = False
    goalid = eachgoal['goalid']
    #print goalid
    jsonanswertag = eachgoal['answertag']
    #print jsonanswertag
    jsonresulttag = eachgoal['resulttag']
    (resulttagtarget, resulttag) = jsonresulttag.split('.')
    #print jsonresulttag
    # answer=<string> and goal_type=matchacross have been checked (not allowed)
    # during parsing of goals
    (use_target, answertagstring) = jsonanswertag.split('.')
    #print use_target
    #print answertagstring
    fulltimestamp = None
    # MatchAcross - Process each file against other files with different timestamp
    # until match or not found
    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)

        #print results
        if results == {}:
            # empty - skip
            continue

        try:
            resulttagresult = results[resulttag]
        except:
            #print('processMatchAcross: %s not found in file %s' % (resulttag, outputjsonfile))
            continue
        #print resulttagresult
        try:
            timestampend = results['PROGRAM_ENDTIME']
        except:
            print('processMatchAcross: PROGRAM_ENDTIME not found in file %s' % outputjsonfile)
            exit(1)
        fulltimestamp = '%s-%s' % (ts, timestampend)

        for ts2 in result_sets.getStamps():
            # ensure different time stamp
            if ts == ts2:
                continue
            #print "processMatchAcross Output 2 json %s" % outputjsonfile
            results2 = result_sets.getSet(ts2)
            try:
                current_answer = results2[answertagstring]
            except KeyError:
                continue

            #print "Correct answer is (%s)" % current_answer

            found = compare_result_answer(resulttagresult, current_answer, eachgoal['goaloperator'])
            if found:
                #print "resulttagresult is (%s) matches answer (%s)" % (resulttagresult, current_answer)
                goal_times.addGoal(goalid, fulltimestamp, True)
                return
 
    # All file processed - still not found
    if not found:
        #print "processMatchAcross failed"
        goal_times.addGoal(goalid, fulltimestamp, False)

def handle_expression(resulttag, json_output, logger):
    result = None
    if resulttag.startswith('(') and resulttag.endswith(')'):
        express = resulttag[resulttag.find("(")+1:resulttag.find(")")]
        for tag in json_output:
            logger.DEBUG('is tag %s in express %s' % (tag, express))
            if tag in express:
                if json_output[tag] != None:
                    express = express.replace(tag, json_output[tag])
                else:
                    return None
        try:
            logger.DEBUG('try eval of <%s>' % express)
            result = evalExpress.eval_expr(express)
        except:
            logger.ERROR('could not evaluation %s, which became %s' % (resulttag, express))
            sys.exit(1)
    else:
        logger.ERROR('handleExpress called with %s, expected expression in parens' % resulttag)
    return result

        
def processMatchAny(result_sets, eachgoal, goal_times, logger):
    #print "Inside processMatchAny"
    #logger.DEBUG("Inside processMatchAny")
    found = False
    goalid = eachgoal['goalid']
    #print goalid
    jsonanswertag = eachgoal['answertag']
    logger.DEBUG('jsonanswertag %s' % jsonanswertag)
    jsonresulttag = eachgoal['resulttag']
    (resulttagtarget, resulttag) = jsonresulttag.split('.')
    logger.DEBUG('jsonresulttag %s' % jsonresulttag)
    # Handle special case 'answer=<string>'
    one_answer = False
    if '=' in jsonanswertag:
        (answertag, onlyanswer) = jsonanswertag.split('=')
        current_onlyanswer = onlyanswer.strip()
        # Change to one_answer = True
        one_answer = True
        #print "Current onlyanswer is (%s)" % current_onlyanswer
    else:
        # No more answer.config (parameter or parameter_ascii will become answer=<value> already)
        (use_target, answertagstring) = jsonanswertag.split('.')
        #print use_target
        #print answertagstring

    # for processMatchAny - Process all files regardless of match found or not found
    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)
        if results == {}:
            # empty - skip
            print('empty for ts %s' % ts)
            continue

        if resulttag.startswith('('):
            resulttagresult = str(handle_expression(resulttag, results, logger))
            logger.DEBUG('from handle_expression, got %s' % resulttagresult)
        else:
            try:
                resulttagresult = results[resulttag]
            except KeyError:
                logger.DEBUG('%s not found in file %s' % (resulttag, ts))
                continue
        if resulttagresult == None:
            continue 
        #print resulttagresult
        try:
            timestampend = results['PROGRAM_ENDTIME']
        except KeyError:
            logger.ERROR('processMatchAny: PROGRAM_ENDTIME not found in file %s' % ts)
            exit(1)
        fulltimestamp = '%s-%s' % (ts, timestampend)
        if one_answer:
            #logger.DEBUG("Correct answer is (%s) result (%s)" % (current_onlyanswer, resulttagresult))
            found = compare_result_answer(resulttagresult, current_onlyanswer, eachgoal['goaloperator'])
            goal_times.addGoal(goalid, fulltimestamp, found)
        else:
            if answertagstring not in results:
                logger.ERROR('%s not in results %s' % (answertagstring, str(results)))
                sys.exit(1)
            answertagresult = results[answertagstring]
            current_answer = answertagresult.strip()
            #logger.DEBUG("Correct answer is (%s) result (%s)" % (current_answer, resulttagresult))
            found = compare_result_answer(resulttagresult, current_answer, eachgoal['goaloperator'])
            goal_times.addGoal(goalid, fulltimestamp, found)

def processValue(result_sets, eachgoal, grades, logger):
    ''' assign the grade the most recent non-NONE result '''
    goalid = eachgoal['goalid']
    #print goalid
    jsonanswertag = eachgoal['answertag']
    #print jsonanswertag
    resulttag = eachgoal['resulttag']
    if resulttag.startswith('result.'):
       resulttag = resulttag[len('result.'):]

    value = None
    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)

        if results == {}:
            # empty - skip
            continue

        try:
            resulttagresult = results[resulttag]
        except KeyError:
            #print('processCount: %s not found in file %s' % (resulttag, outputjsonfile))
            continue
        if resulttagresult != None:
            value = resulttagresult
    #print 'count is %d' % count
    grades[goalid] = value
 
def processCount(result_sets, eachgoal, grades, logger):
    #print "Inside processCount"
    count = 0
    goalid = eachgoal['goalid']
    #print goalid
    jsonanswertag = eachgoal['answertag']
    #print jsonanswertag
    resulttag = eachgoal['resulttag']
    if resulttag.startswith('result.'):
       resulttag = resulttag[len('result.'):]
 
    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)

        if results == {}:
            # empty - skip
            continue

        try:
            resulttagresult = results[resulttag]
        except KeyError:
            #print('processCount: %s not found in file %s' % (resulttag, outputjsonfile))
            continue
        if resulttagresult != None:
            if 'goaloperator' in eachgoal and len(eachgoal['goaloperator']) > 0:
                jsonanswertag = eachgoal['answertag']
                #print jsonanswertag
                jsonresulttag = eachgoal['resulttag']
                print 'tag is %s' %  jsonresulttag
                #(resulttagtarget, resulttag) = jsonresulttag.split('.')
                #print jsonresulttag
                # Handle special case 'answer=<string>'
                one_answer = False
                if '=' in jsonanswertag:
                    (answertag, onlyanswer) = jsonanswertag.split('=')
                    current_onlyanswer = onlyanswer.strip()
                    # Change to one_answer = True
                    one_answer = True
                    #print "Current onlyanswer is (%s)" % current_onlyanswer
                else:
                    (use_target, answertagstring) = jsonanswertag.split('.')
                    #print use_target
                    #print answertagstring
                if one_answer:
                    #print "Correct answer is (%s) result (%s)" % (current_onlyanswer, resulttagresult)
                    found = compare_result_answer(resulttagresult, current_onlyanswer, eachgoal['goaloperator'])
                else:
                    if answertagstring not in results:
                        logger.ERROR('%s not in results %s' % (answertagstring, str(results)))
                        sys.exit(1)
                    answertagresult = results[answertagstring]
                    current_answer = answertagresult.strip()
                    found = compare_result_answer(resulttagresult, current_answer, eachgoal['goaloperator'])
                if found:
                    count += 1
            else:
                count += 1
    #print 'count is %d' % count
    grades[goalid] = count

def processExecute(results, eachgoal, goal_times):
    #print "Inside processExecute"
    found = False
    goalid = eachgoal['goalid']
    #print goalid
    executefile = eachgoal['goaloperator']
    #print executefile
    jsonanswertag = eachgoal['answertag']
    #print jsonanswertag
    jsonresulttag = eachgoal['resulttag']
    (resulttagtarget, resulttag) = jsonresulttag.split('.')
    #print jsonresulttag
    # Handle special case 'answer=<string>'
    one_answer = False
    if '=' in jsonanswertag:
        (answertag, onlyanswer) = jsonanswertag.split('=')
        current_onlyanswer = onlyanswer.strip()
        # Change to one_answer = True
        one_answer = True
        #print "Current onlyanswer is (%s)" % current_onlyanswer
    else:
        print('processExecute: expecting answertag to be the parameterized value')
        exit(1)

    # for processExecute - Process all files regardless of match found or not found
    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)
        if results == {}:
            # empty - skip
            continue

        try:
            resulttagresult = results[resulttag]
        except KeyError:
            print('processExecute: %s not found in file %s' % (resulttag, outputjsonfile))
            continue
        #print resulttagresult
        
        try:
            timestampend = results['PROGRAM_ENDTIME']
        except KeyError:
            print('processExecute: PROGRAM_ENDTIME not found in file %s' % outputjsonfile)
            exit(1)
        fulltimestamp = '%s-%s' % (ts, timestampend)

        #print "Correct answer is (%s) result (%s)" % (current_onlyanswer, resulttagresult)
        #found = compare_result_answer(resulttagresult, current_onlyanswer, eachgoal['goaloperator'])

        command = "%s %s %s" % (executefile, resulttagresult, current_onlyanswer)
        #print("Command to execute is (%s)" % command)
        result = subprocess.call(command, shell=True)
        if result:
            #print "processExecute return 1"
            goal_times.addGoal(goalid, fulltimestamp, True)
        else:
            #print "processExecute return 0"
            goal_times.addGoal(goalid, fulltimestamp, False)

def processTrueFalse(result_sets, eachgoal, goal_times):
    #print "Inside processTrueFalse"
    found = False
    goalid = eachgoal['goalid']
    #print goalid
    resulttag = eachgoal['resulttag']
    #print resulttag
    #print eachgoal

    for ts in result_sets.getStamps():
        results = result_sets.getSet(ts)

        if results == {}:
            # empty - skip
            continue

        try:
            resulttagresult = results[resulttag]
        except KeyError:
            #print('processTrueFalse: %s not found in file %s' % (resulttag, outputjsonfile))
            continue
        if resulttagresult == None:
            continue
        
        try:
            timestampend = results['PROGRAM_ENDTIME']
        except KeyError:
            print('processTrueFalse: PROGRAM_ENDTIME not found in file %s' % outputjsonfile)
            exit(1)
        fulltimestamp = '%s-%s' % (ts, timestampend)
        #print('compare %s operator %s' % (resulttagresult, eachgoal['goaltype']))
        found = compare_result_answer(resulttagresult, None, eachgoal['goaltype'])
        goal_times.addGoal(goalid, fulltimestamp, found)
 
def countTrue(the_goals, current_goals):
    #print('current goals %s' % str(current_goals))
    count = 0
    for item in current_goals:
        item = item.strip()
        if item in the_goals:
            if current_goals[item]:
                count += 1
                #print('item %s true count now %d' % (item, count))
                the_goals.remove(item)
    return count
    
def processCountGreater(eachgoal, goal_times):
    goalid = eachgoal['goalid']
    try:
        value = int(eachgoal['answertag'])
    except:
        print('ERROR: Grader.py could not parse int from %s in %s' % (eachgoal['answertag'], eachgoal))
        exit(1)
    ''' note, not a boolean string, TBD change name to more generic '''
    subgoal_list = eachgoal['boolean_string']
    # Process all goals_ts_id dictionary
    goalid = eachgoal['goalid']
    #print('countGreater, value %d list %s' % (value, subgoal_list))
    true_count = 0
    the_list = subgoal_list[subgoal_list.find("(")+1:subgoal_list.find(")")]
    the_goals = the_list.strip().split(',')
    the_goals = [x.strip() for x in the_goals]
    goals_ts_id = goal_times.getGoalTimeStampId()
    for timestamppart, current_goals in goals_ts_id.iteritems():
        true_count += countTrue(the_goals, current_goals)
        #print('true_count now %d' % true_count)
    is_greater = False
    if true_count > value:
        is_greater = True
    #print('true_count is %d' % true_count)
    #print('countGreater result is %r' % is_greater)
    goal_times.addGoal(goalid, default_timestamp, is_greater)
    

def processTemporal(eachgoal, goal_times, logger):
    goal1tag = eachgoal['goal1tag']
    goal2tag = eachgoal['goal2tag']
    goalid = eachgoal['goalid']
    logger.DEBUG("goal1tag is (%s) and goal2tag is (%s)" % (goal1tag, goal2tag))
    # Make sure goal1tag and goal2tag is in goals_id_ts
    if not goal_times.hasGoal(goal1tag) and eachgoal['goaltype'] != 'time_not_during':
        logger.DEBUG("warning: goal1tag (%s) does not exist in goalTimes\n" % (goal1tag))
        return
    if not goal_times.hasGoal(goal2tag):
        logger.DEBUG("warning: goal2tag (%s) does not exist!\n" % goal2tag)
        return
    goals_tag1 = goal_times.getGoal(goal1tag)
    goals_tag2 = goal_times.getGoal(goal2tag)
    #print "Goals tag1 is "
    #print goals_tag1
    #print "Goals tag2 is "
    #print goals_tag2
    if eachgoal['goaltype'] == "time_before":
        eval_time_result = evalTimeBefore(goals_tag1, goals_tag2)
        # if eval_time_result is False - that means, can't find the following condition:
        # (1) goals_tag1 is True and goals_tag2 is True
        # (2) goal1start <= goal2start
        goal_times.addGoal(goalid, default_timestamp, eval_time_result)
    elif eachgoal['goaltype'] == "time_during":
        logger.DEBUG('eval for %s %s' % (goals_tag1, goals_tag2))
        eval_time_result = evalTimeDuring(goals_tag1, goals_tag2, logger)
        for ts in eval_time_result:
            goal_times.addGoal(goalid, ts, eval_time_result[ts])
            
                
        # if eval_time_result is False - that means, can't find the following condition:
        # (1) goals_tag1 is True and goals_tag2 is True
        # (2) goal2start (%s) <= goal1start (%s) <= goal2end (%s)
    elif eachgoal['goaltype'] == "time_not_during":
        logger.DEBUG('eval time_not_during for %s %s' % (goals_tag1, goals_tag2))
        eval_time_result = evalTimeNotDuring(goals_tag1, goals_tag2, logger)
        for ts in eval_time_result:
            goal_times.addGoal(goalid, ts, eval_time_result[ts])


def processBoolean(eachgoal, goal_times, logger):
    glist = goal_times.getGoalList()
    t_string = eachgoal['boolean_string']
    evalBooleanResult = None
    goalid = eachgoal['goalid']
    # Process all goals_ts_id dictionary
    goals_ts_id = goal_times.getGoalTimeStampId()
    for timestamppart, current_goals in goals_ts_id.iteritems():
        if timestamppart != default_timestamp or len(goals_ts_id)==1:
            logger.DEBUG('eval %s against %s tspart %s' % (t_string, str(current_goals), timestamppart))
            evalBooleanResult = evalBoolean.evaluate_boolean_expression(t_string, current_goals, logger, glist)
            if evalBooleanResult is not None:
                logger.DEBUG('bool evaluated to %r' % evalBooleanResult)
                goal_times.addGoal(goalid, timestamppart, evalBooleanResult)
    # if evalBooleanResult is None - means not found
    if evalBooleanResult is None:
        #logger.DEBUG('processBoolean evalBooleanResult is None, goalid %s goal_id_ts %s' % (goalid, goals_ts_id))
        logger.DEBUG('processBoolean evalBooleanResult is None, goalid %s ' % (goalid))
        goal_times.addGoal(goalid, default_timestamp, False)

class ResultSets():
    def addSet(self, result_set, ts, goal_times):
        #print('addSet')
        if 'PROGRAM_ENDTIME' in result_set:
            fulltimestamp = '%s-%s' % (ts, result_set['PROGRAM_ENDTIME'])
        else:
            fulltimestamp = '%s-0' % (ts)
            
        if ts in self.result_sets:
            print('ts')
            ''' add boolean results to goals '''
            for key in result_set:
                print('look at %s, val %s' % (key, result_set[key]))
                self.result_sets[ts][key] = result_set[key]
                if isinstance(result_set[key], bool):
                    print 'is bool ts is %s' % ts
                    goal_times.addGoal(key, fulltimestamp, result_set[key])
                    
        else:
            self.result_sets[ts] = result_set
            for key in result_set:
                if isinstance(result_set[key], bool):
                    #print 'ts is %s' % ts
                    goal_times.addGoal(key, fulltimestamp, result_set[key])

    def __init__(self, result_file_list, goal_times):
        ''' result_file_list are full paths '''
        self.result_sets = {}
        self.latest = None
        for result_file in result_file_list:
            fname = os.path.basename(result_file)
            #print('addSet %s' % fname)
            if '.' in fname:
                dumb, ts = fname.rsplit('.',1)
                if self.latest is None or ts > self.latest:
                    self.latest = ts
                result_set = getJsonOut(result_file)
                self.addSet(result_set, ts, goal_times)
            elif fname.endswith('_ts') or fname.endswith('_td'):
                result_set_set = getJsonOutTS(result_file)
                for ts in result_set_set:
                    self.addSet(result_set_set[ts], ts, goal_times)
            else:
                result_set = getJsonOut(result_file)
                self.addSet(result_set, 'default', goal_times)

    def getSet(self, ts):
        return self.result_sets[ts]
    def getLatest(self):
        return self.result_sets[self.latest], self.latest
    def getStamps(self):
        return list(self.result_sets.keys())
         

def finalGoalValue(goalid, grades, goal_times):
    if goalid in grades:
        # already there, must be calculated value
        return
    #print "goalid is (%s)" % goalid
    current_goals_result = False
    goals_id_ts = goal_times.getGoalIdTimeStamp()
    for current_goals, timestamp in goals_id_ts.iteritems():
        #print "current_goals is "
        #print current_goals
        if current_goals == goalid:
            current_value = False
            # Use goals_ts_id for processing 
            # - if found on any timestamp then True
            # - if not found on any timestamp then False
            for key, value in timestamp.iteritems():
                #print "Key is (%s) - value is (%s)" % (key, value)
                if value:
                    current_value = True
                    break
            current_goals_result = current_value
            break
    #print('assign grades[%s] %r' % (goalid, current_goals_result))
    grades[goalid] = current_goals_result

# Process Lab Exercise
def processLabExercise(studentlabdir, labidname, grades, goals, bool_results, goal_times, logger):
    #print('processLabExercise studentlabdir %s ' % (studentlabdir))
    #print "Goals JSON config is"
    #print goals
    #for eachgoal in goals:
    #    print "Current goal is "
    #    print eachgoal
    #    print "    goalid is (%s)" % eachgoal['goalid']
    #    print "    goaltype is (%s)" % eachgoal['goaltype']
    #    print "    answertag is (%s)" % eachgoal['answertag']
    #    print "    resulttag is (%s)" % eachgoal['resulttag']
    #    print ""
    RESULTHOME = '%s/%s' % (studentlabdir, ".local/result/")
    #print RESULTHOME
    if not os.path.exists(RESULTHOME):
        sys.stderr.write("ERROR: missing RESULTHOME (%s)\n" % RESULTHOME)
        sys.exit(1)

    ''' Read result sets '''

    outjsonfnamesstring = '%s/%s/%s*' % (studentlabdir, ".local/result/", labidname)

    outjsonfnames = glob.glob(outjsonfnamesstring)
    result_sets = ResultSets(outjsonfnames, goal_times)

    # Go through each goal for each student
    for eachgoal in goals:
        logger.DEBUG('goal is %s type %s' % (eachgoal['goalid'], eachgoal['goaltype']))

        if eachgoal['goaltype'] == "matchany":
            processMatchAny(result_sets, eachgoal, goal_times, logger)
        elif eachgoal['goaltype'] == "matchlast":
            processMatchLast(result_sets, eachgoal, goal_times)
        elif eachgoal['goaltype'] == "matchacross":
            processMatchAcross(result_sets, eachgoal, goal_times)
        elif eachgoal['goaltype'] == "execute":
            processExecute(result_sets, eachgoal, goal_times)
        elif eachgoal['goaltype'] == "boolean":
            processBoolean(eachgoal, goal_times, logger)
        elif eachgoal['goaltype'] == "time_before" or \
             eachgoal['goaltype'] == "time_during" or \
             eachgoal['goaltype'] == "time_not_during":
            processTemporal(eachgoal, goal_times, logger)
        elif eachgoal['goaltype'] == "count_greater":
            processCountGreater(eachgoal, goal_times)
        elif eachgoal['goaltype'] == "count":
            processCount(result_sets, eachgoal, grades, logger)
        elif eachgoal['goaltype'] == "value":
            processValue(result_sets, eachgoal, grades, logger)
        elif eachgoal['goaltype'].startswith('is_'):
            processTrueFalse(result_sets, eachgoal, goal_times)
        else:
            sys.stdout.write("Error: Invalid goal type: %s\n eachgoal is %s", (eachgoal['goaltype'], str(eachgoal)))
            sys.exit(1)


    #print "Goals - id timestamp : "
    #print goals_id_ts
    #for current_goals, timestamp in goals_id_ts.iteritems():
    #     print "-----"
    #     print current_goals
    #     print timestamp
    #print "Goals - timestamp id : "
    #print goals_ts_id

    # Now generate the grades - based on goalid
    for eachgoal in goals:
        goalid = eachgoal['goalid']
        finalGoalValue(goalid, grades, goal_times)
    for result in bool_results:
        finalGoalValue(result, grades, goal_times)

    #print grades

    return 0

# Usage: ProcessStudentLab <studentlabdir> <labidname>
#   return a dictionary of grades for this student.
# Arguments:
#     <studentlabdir> - directory containing the student lab work
#                    extracted from zip file (done in instructor.py)
#     <labidname> - labidname should represent filename of output json file
def ProcessStudentLab(studentlabdir, labidname, logger):
    # Goals
    goal_times = GoalTimes()
    grades = OrderedDict()
    resultsdir = os.path.join(studentlabdir, '.local','result')
    try:
        os.makedirs(resultsdir)
    except:
        pass
    goalsjsonfname = os.path.join(resultsdir,'goals.json')
    with open(goalsjsonfname) as fh:
        goals = json.load(fh)

    boolresultsfname = os.path.join(resultsdir,'bool_results.json')
    with open(boolresultsfname) as fh:
        bool_results = json.load(fh)

    processLabExercise(studentlabdir, labidname, grades, goals, bool_results, goal_times, logger)
    
    return grades

# Usage: Grader.py <studentlabdir> <labidname>
# Arguments:
#     <studentlabdir> - directory containing the student lab work
#                    extracted from zip file (done in instructor.py)
#     <labidname> - labidname should represent filename of output json file
def main():
    #print "Running Grader.py"
    if len(sys.argv) != 3:
        sys.stderr.write("Usage: Grader.py <studentlabdir> <labidname>\n")
        return 1

    studentlabdir = sys.argv[1]
    labidname = sys.argv[2]
    #print "Inside main, about to call ProcessStudentLab "

    logger = InstructorLogging.InstructorLogging("/tmp/instructor.log")
    ProcessStudentLab(studentlabdir, labidname, logger)

if __name__ == '__main__':
    sys.exit(main())


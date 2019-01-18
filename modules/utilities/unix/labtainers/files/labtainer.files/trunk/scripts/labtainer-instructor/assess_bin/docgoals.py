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

'''
Parse goals.config and results.config and display embedded documentation
intended to describe the meaning of each goal.


'''

def doDoc(fpath):
  lines = []
  with open(fpath) as fh:
    #for line in fh:
    leftover=False
    while True:
        if not leftover:
            line = fh.readline()
        else:            
            leftover = False
        if line is None or len(line)==0:
            break
        if line.startswith('#'):
            rest = line[1:]
            if ':' in rest:
                directive, text = rest.split(':', 1) 
                directive = directive.strip()
                text = text.strip()
                if directive == 'DOC':
                    #for line in fh:
                    while True:
                        line = fh.readline()
                        if line is None or len(line)==0:
                            break
                        if line.startswith('#'):
                            text = text + '\n\t\t'+ line[1:].strip()
                        else:
                            line = line.strip()
                            if len(line) > 0:
                                parts = line.split()
                                sym = parts[0]
                                #print('\t%s: %s' % (sym, text)) 
                                lines.append('\t%s: %s' % (sym, text)) 
                                break
                elif directive == 'GROUP':
                    symbols = []
                    #for line in fh:
                    while True:
                        line = fh.readline()
                        if line is None or len(line)==0:
                            break
     
                        if line.startswith('#'):
                            if len(symbols) > 0:
                                leftover = True
                                break
                            text = text + '\n\t'+ line[1:].strip() 
                        else:
                            line = line.strip()
                            if len(line) > 0:
                                parts = line.split()
                                sym = parts[0]
                                symbols.append(sym)
                            else:
                                break
                    symlist = ', '.join(symbols) 
                    #print('\t%s: %s' % (symlist, text))
                    lines.append('\t%s: %s' % (symlist, text))
                elif directive == 'SUM':
                    #for line in fh:
                    while True:
                        line = fh.readline()
                        if line is None or len(line)==0:
                            break
                        if line.startswith('#') and len(line.strip())>1:
                            text = text + '\n\t\t'+ line[1:].strip()
                        else:
                            #print('\t'+text)
                            lines.append('\t'+text)
                            break 
    return "\n".join(lines)

def getGoalInfo(instr_config_path):
    header='What is automatically assessed for this lab:\n'
    results_path = os.path.join(instr_config_path, 'results.config')
    results_summary = doDoc(results_path)
    goals_path = os.path.join(instr_config_path, 'goals.config')
    summary = doDoc(goals_path)
    return header+results_summary+'\n'+summary+'\n'

if __name__ == "__main__":
    labname = sys.argv[1]
    print getGoalInfo(labname)

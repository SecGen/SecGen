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

import os
import sys
import re
from netaddr import *
import LabtainerLogging
import ParseLabtainerConfig
import labutils
import collections

def isalphadashscore(name):
    # check name - alphanumeric,dash,underscore
    return re.match(r'^[a-zA-Z0-9_-]*$', name)

class ParseStartConfig():
    def __init__(self, fname, labname, labtainer_config, logger, skip_networks=True, servers=None, clone_count=None):
        self.containers = collections.OrderedDict()
        self.subnets    = {} # dictionary of subnets 
        self.labname = labname
        self.caller = 'student'
        self.host_home_xfer= "" # HOST_HOME_XFER - directory to transfer artifact to/from containers
        self.lab_master_seed= None # LAB_MASTER_SEED - this is the master seed string for to this laboratory
        self.grade_container = None # GRADE_CONTAINER - this is where the instructor performs the grading
        self.logger = logger
        self.fname = fname
        self.skip_networks = skip_networks
        self.labtainer_config = labtainer_config
        self.clone_count = clone_count
        # CHECKWORK, set to 'NO' if students should not be able to check their work for this lab
        self.checkwork = 'yes'
        # COLLECT_DOCS - this optional setting indicates whether to collect lab's docs directory or not
        # default to NO (i.e., do not collect)
        self.collect_docs = 'yes'
        self.lan_hosts = {}

        if not os.path.exists(fname):
            self.logger.ERROR("Config file %s does not exists!\n" % fname)
            sys.exit(1)

        self.get_configs(fname)
        self.multi_user = None
        ''' determine if running as a distributed Labtainers, or many clients on a single VM '''
        if self.clone_count > 0: 
            self.multi_user = 'clones'
        elif servers is not None:
            self.multi_user = servers 
        self.finalize()
        self.validate()
        self.logger.DEBUG('Completed reload from %s' % fname)

    class Container():
        def __init__(self, name, logger):
            self.name       = name
            self.terminals  = 1
            self.xterm      = None
            self.user       = ""
            self.password       = ""
            self.hostname       = ""
            self.image_name = ""
            self.full_name  = ""
            self.container_nets = {} #dictionary of name and ip addr 
            self.script = "bash"
            self.x11 = "no"
            self.registry = None
            self.terminal_group = None
            self.add_hosts = []
            self.no_privilege = 'no'
            self.clone_copies = None # Dynamic, number of clones to be created.
            self.clone = None  # Number of clones of this component to create.  
            self.client = None # Used for distributed labtainers, indicates container is a client
            self.from_image = None
            self.enable = None
            self.disable = None
            self.logger = logger

        def add_net(self, name, ipaddr):
            self.container_nets[name] = ipaddr

        def validate(self, valid_networks=set(), skip_networks = False):
            self.terminals = int(self.terminals) #replace with something smarter
          
            if '=' in self.name: # TODO: do we still need this?
                self.logger.ERROR('Character "=" is not allowed in container name (%s)\n' % self.name)
                exit(1)
            for name, addr in self.container_nets.items():
                tlan = name
                if ':' in name:
                    tlan = name.split(':')[0]
                if tlan not in valid_networks:
                    self.logger.ERROR('Container %s cannot be added to undefined network %s\n' % (self.full_name, tlan))
                    exit(1)
                if not skip_networks:
                    if ':' in addr:
                        addr, mac = addr.split(':',1)
                        if not re.match("[0-9a-f]{2}([-:])[0-9a-f]{2}(\\1[0-9a-f]{2}){4}$", mac.lower()):
                            self.logger.ERROR('bad MAC address %s in %s\n' % (mac, name))
                            exit(1)
                    elif '+' in addr:
                        addr, adjust = addr.split('+')
                        if adjust.lower() != 'clone' and adjust.lower() != 'clone_mac':
                            self.logger.ERROR('bad adjustment syntax for network definition of %s, expected "CLONE", got %s' % (name, adjust))
                            exit(1)
                    if addr != 'auto' and addr != 'auto_mac':
                        try:
                            IPAddress(addr)
                        except :
                            self.logger.ERROR('bad ip addr %s in %s\n' % (addr, name))
                            exit(1)

    class Subnet():
        def __init__(self, name, logger):
            self.name   = name
            self.mask = 0
            self.gateway = 0
            self.macvlan = None
            self.macvlan_ext = None
            self.macvlan_use = None
            self.ip_range = None
            self.logger = logger

        def validate(self):
            if not isalphadashscore(self.name):
                self.logger.ERROR('bad subnet name %s \n' % (self.name))
                exit(1)
            try:
                IPNetwork(self.mask)
            except:
                self.logger.ERROR('bad ip subnet %s for subnet %s\n' % (self.mask, self.name))
                exit(1)
            if not IPAddress(self.gateway) in IPNetwork(self.mask):
                self.logger.ERROR('network: %s Gateway IP (%s) not in subnet for SUBNET line(%s)!\n' % 
                    (self.name, self.gateway, self.mask))
                exit(1)

    def add_if_new(self, name, location, thing):
        if name in location:
            self.logger.ERROR("Fatal. '%s' already defined." % name)
            exit(1)
        location[name] = thing

    def get_configs(self, fname):
        """Reads the new config format. There is basically no format validation so 
           this accidentally supports a much more flexible format right now, which 
           is bad. It does check for unknown config options, which is good. The main
           advantage is that I think the parsing method should be easy to extend."""
        active      = None
        defaults_ok = {"network","container", "global_settings"}
        with open(fname, "r") as f:
            for line in f:
                linestrip = line.strip()
                if not linestrip or linestrip.startswith("#"):
                    continue
                keyval = linestrip.split(None,1)    
                key = keyval[0].lower()
                if len(keyval) > 1:
                    if key == "user" or key == "xterm" or key == "password":
                        # DO NOT change case for 'user' or 'xterm'
                        val = keyval[1]
                    else:
                        val = keyval[1].lower()
                elif key in defaults_ok:
                    val = "default"
                else:
                    self.logger.ERROR("Fatal. Missing value for: %s" % line)
                    exit(1)

                if key == "global_settings":
                    active = self
                elif key == "network":
                    self.add_if_new(val, self.subnets, self.Subnet(val, self.logger))
                    active = self.subnets[val]
                    self.lan_hosts[val] = []
                elif key == "container":
                    if val in self.containers:
                        self.logger.ERROR('Container %s already defined' % val)
                        exit(1)
                    self.containers[val] = self.Container(val, self.logger)
                    self.logger.DEBUG('added container %s' % val)
                    active = self.containers[val]
                elif key == 'add-host':
                    active.add_hosts.append(val)
                elif hasattr(active, key):
                    setattr(active, key, val) 
                else:
                    try:
                        active.add_net(key,val)
                        lan_host = '%s:%s' % (active.name, val)
                        tlan = key
                        if ':' in key:
                            tlan = key.split(':')[0]
                        self.lan_hosts[tlan].append(lan_host)
                    except:
                        self.logger.ERROR("Fatal. Can't understand config setting: %s" % line)
                        exit(1)

    def validate(self):
        """ Checks to make sure we have all the info we need from the user."""

        if not self.collect_docs:
            # COLLECT_DOCS - this optional setting indicates whether to collect lab's docs directory or not
            self.collect_docs = "no"
        else:
            if self.collect_docs.lower() != "yes" and self.collect_docs.lower() != "no":
                self.logger.ERROR("Unexpected collect_docs value in ParseStartConfig module : %s\n" % self.collect_docs)
                exit(1)
        
        if not self.host_home_xfer:
            self.logger.ERROR("Missing host_home_xfer in start.config!\n")
            exit(1)
        
        if not self.lab_master_seed:
           self.logger.ERROR("Missing lab_master_seed in start.config!\n")
           exit(1)

        if not self.grade_container:
            self.logger.ERROR("Missing grade_container in start.config!\n")
            exit(1)
        
        if not self.skip_networks:
            for subnet in self.subnets.values():
                subnet.validate()

        for container in self.containers.values():
            container.validate(self.subnets.keys(), self.skip_networks)



    def finalize(self):
        """Combines info provided by user with what we already know about the
           lab to get the final settings we want."""
        # fixing up global parameters
        self.host_home_xfer = os.path.join(self.host_home_xfer,self.labname)
        self.lab_master_seed = self.labname + self.lab_master_seed
        if self.grade_container == "default":
            self.grade_container = self.labname + "." + self.caller 
        else:
            self.grade_container = self.labname + "." + self.grade_container + "." + self.caller 

        ''' fix macvlan networks, assign use_macvan value based on whether ...'''
        for net in self.subnets:
            if self.subnets[net].macvlan_ext is not None:
                try:
                    iface_num = int(self.subnets[net].macvlan_ext)
                    use = labutils.getFirstUnassignedIface(iface_num)
                    if use is None:
                        self.logger.ERROR('MACVLAN requested, but not %s unassigned interfaces' % iface_num)
                        exit(1)
                except ValueError:
                    use = self.subnets[net].macvlan_ext
                self.subnets[net].macvlan_use = use
            elif self.multi_user is not None:
                try:
                    iface_num = int(self.subnets[net].macvlan)
                    use = labutils.getFirstUnassignedIface(iface_num)
                    if use is None:
                        self.logger.ERROR('MACVLAN requested, but not %s unassigned interfaces' % iface_num)
                        exit(1)
                except ValueError:
                    use = self.subnets[net].macvlan
                self.subnets[net].macvlan_use = use

        use_test_registry = os.getenv('TEST_REGISTRY')
        # fixing up container parameters
        for name in self.containers:
            if name == "default": namestr = ""
            else:
                namestr = "." + name
            full  = self.labname + namestr + "." + self.caller 
            if self.containers[name].from_image is None:
                image = self.labname + namestr + "." + self.caller
            else:
                image = self.containers[name].from_image + "." + self.caller
                if self.containers[name].user == '':
                    try:
                        from_lab, from_container = self.containers[name].from_image.split('.')
                    except:
                        self.logger.ERROR('bad from_image value %s' % self.containers[name].from_image)
                        exit(1)
                    if from_lab == self.labname:
                        self.containers[name].user = self.containers[from_container].user
                        self.containers[name].password = self.containers[from_container].password
                        #print('from_lab <%s>, user now %s  bs is %s' %(from_lab, self.containers[name].user, self.containers[from_container].user))
                    else:
                        self.logger.ERROR('Use of FROM_IMAGE from different lab requires explicit USER (which must be manually gotten from other lab :{')
                        exit(1)
            if self.containers[name].full_name == "":
               self.containers[name].full_name = full
            if self.containers[name].image_name == "":
               self.containers[name].image_name = image 
            if self.containers[name].hostname == "":
               self.containers[name].hostname = name
            if self.containers[name].password == "":
               self.containers[name].password = self.containers[name].user
            if self.containers[name].script == "none":
               self.containers[name].script = "";
            if use_test_registry is not None and (use_test_registry.lower() == 'yes' or use_test_registry.lower() == 'true'):
                self.containers[name].registry = self.labtainer_config.test_registry
            elif self.containers[name].registry == None:
                self.containers[name].registry = self.labtainer_config.default_registry
            if self.clone_count is not None and self.containers[name].client == 'yes':
                if self.containers[name].clone is not None:
                    self.logger.ERROR('Cannot specify clone_count for container having CLONE set in the start.config file')
                    exit(1)
                else:
                    self.containers[name].clone_copies = self.clone_count
            if self.containers[name].clone is not None:
                self.container[name].clone_copies = self.contaienrs[name].clone

    def show_current_settings(self):
        bar = "="*80
        print bar
        print("Global configuration settings:")
        print bar
        for key, val in self.__dict__.items():
            if type(val) == type({}): val = len(val)
            print "\t" + str(key) + ": " + str(val)
        print "\n"+bar
        print("Network configuration settings:")
        print bar
        for name, network in self.subnets.items():
            print "name: " + name 
            for key, val in network.__dict__.items():
                if type(val) == type({}): val = len(val)
                print "\t" + str(key) + ": " + str(val)
            print ""
        print "\n"+bar
        print("Container configuration settings:")
        print bar
        for name, container in self.containers.items():
            print "name: " + name 
            for key, val in container.__dict__.items():
                if type(val) == type({}): val = len(val)
                print "\t" + str(key) + ": " + str(val)
            print ""
             
if __name__ == '__main__':
    start_config = ParseStartConfig(*sys.argv[1:])
    start_config.show_current_settings()

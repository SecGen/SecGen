import filecmp
import glob
import json
import md5
import os
import shutil
import re
import subprocess
import sys
import time
import zipfile
import ParseStartConfig
import ParseLabtainerConfig
import datetime
import getpass
import socket
import fcntl
import struct
import threading
import LabtainerLogging
import LabCount
import shlex
import stat
import traceback
import CheckTars
import BigFiles
import BigExternal
import calendar
import string
import errno
try:
    from dateutil.parser import parse
except:
    pass

instructor_cwd = os.getcwd()
student_cwd = instructor_cwd.replace('labtainer-instructor', 'labtainer-student')
if student_cwd.endswith('labtainer-student'):
    sys.path.append(student_cwd+"/lab_bin")
else:
    # assume from labtainer/distrib
    print('is distrib %s' % os.getcwd())
    sys.path.append('../scripts/labtainer-student/lab_bin')
import ParameterParser
import InspectLocalReg
import InspectRemoteReg


''' logger is defined in whatever script that invokes the labutils '''
global logger
'''
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
'''


# Error code returned by docker inspect
SUCCESS=0
FAILURE=1

''' 
 Version number embeded as a label into each docker image.
 Current framework version (per below) must be at least
 what is found in the image.  This is only used for 
 framework/image compatibility, to tell a user that a given
 lab cannot be run without doing an update.
''' 
framework_version = 3

# Create a directory path based on input path
# Note: Do not create if the input path already exists as a directory
#       If input path is a file, remove the file then create directory
def createDirectoryPath(input_path):
    # if it exist as a directory, do not delete (only delete if it is a file)
    if os.path.exists(input_path):
        # exists but is not a directory
        if not os.path.isdir(input_path):
            # remove file then create directory
            os.remove(input_path)
            os.makedirs(input_path)
        #else:
        #    logger.DEBUG("input_path directory (%s) exists" % input_path)
    else:
        # does not exists, create directory
        os.makedirs(input_path)

def is_valid_lab(lab_path):
    # Lab path must exist and must be a directory
    if os.path.exists(lab_path) and os.path.isdir(lab_path):
        # Assume it is valid lab then
        logger.DEBUG("lab_path directory (%s) exists" % lab_path)
    else:
        logger.ERROR("Invalid lab! lab_path directory (%s) does not exist!" % lab_path)
        #traceback.print_exc()
        #traceback.print_stack()
        sys.exit(1)


def getFirstUnassignedIface(n=1):
    ''' get the nth network iterface that lacks an assigned IP address '''
    iflist = os.listdir('/sys/class/net')
    for iface in sorted(iflist):
        count = 1
        ip = get_ip_address(iface)
        if ip is None and n == count:
            return iface
        count += 1
    return None

def get_ip_address(ifname):
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sp = struct.pack('256s', ifname[:15])
    try:
        fc = fcntl.ioctl(s.fileno(), 0x8915, sp)
    except:
        return None
    return socket.inet_ntoa(fc[20:24])

def get_hw_address(ifname):
    #print('get_hw_address for %s' % ifname)
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    info = fcntl.ioctl(s.fileno(), 0x8927,  struct.pack('256s', ifname[:15]))
    return ':'.join(['%02x' % ord(char) for char in info[18:24]])


def get_new_mac(ifname):
    ''' use last two byte of mac address to generate a new mac
        intended for use on macvlan '''
    # TBD move this hardcoded prefix into some config file?
    preface = '02:43:ac:12'
    my_mac = get_hw_address(ifname)
    parts = my_mac.split(':')
    p1 = parts[4]
    p2 = parts[5]
    full = '%s:%s:%s' % (preface, p1, p2)
    return  full

def isalphadashscore(name):
    # check name - alphanumeric,dash,underscore
    return re.match(r'^[a-zA-Z0-9_-]*$', name)

# get docker0 IP address
def getDocker0IPAddr():
    #return get_ip_address('docker0')
    cmd = "docker inspect -f '{{ .NetworkSettings.IPAddress }}' docker0"
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) == 0:
        ''' is a docker0 master container '''
        if len(output[0].strip()) > 0:
            return output[0].strip()
        else:
            return None
    else:
        return get_ip_address('docker0')

# Parameterize my_container_name container
def ParameterizeMyContainer(mycontainer_name, container_user, container_password, lab_instance_seed, user_email, labname, lab_path, name, image_info):
    retval = True
    ''' copy lab_bin and lab_sys files into .local/bin and / respectively '''
    CopyLabBin(mycontainer_name, container_user, lab_path, name, image_info)
    cmd_path = '/home/%s/.local/bin/parameterize.sh' % (container_user)
    if container_password == "":
        container_password = container_user

    version = '0'
    if image_info is None or image_info.version is None:
        ''' is a build, version -1 '''
        version = '-1'
    else:
        #print(str(image_info))
        if image_info.version is not None:
            version = image_info.version
    command=['docker', 'exec', '-i',  mycontainer_name, cmd_path, container_user, container_password, lab_instance_seed, user_email, labname, mycontainer_name, version ]
    logger.DEBUG("About to call parameterize.sh with : %s" % str(command))
    #return retval 
    child = subprocess.Popen(command, shell=False, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    error_string = child.stderr.read()
    if len(error_string) > 0:
        for line in error_string.splitlines(True):
            if  not line.startswith('[sudo]') and "LC_ALL" not in line and "ENCRYPT_METHOD" not in line:
                logger.ERROR('ParameterizeMyContainer %s' % line)
                retval = False
            else:
                logger.DEBUG(line)
    out_string = child.stdout.read().strip()
    if len(out_string) > 0:
        logger.DEBUG('ParameterizeMyContainer %s' % out_string)
    return retval

# Start my_container_name container
def StartMyContainer(mycontainer_name):
    retval = True
    if IsContainerRunning(mycontainer_name):
        logger.ERROR("Container %s is already running!\n" % (mycontainer_name))
        sys.exit(1)
    command = "docker start %s" % mycontainer_name
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1]) > 0:
        logger.ERROR('StartMyContainer %s' % output[1])
        logger.ERROR('command was %s' % command)
        retval = False
    if len(output[0]) > 0:
        logger.DEBUG(output[0])
    return retval

def AllContainersCreated(container):
    clone_names = GetContainerCloneNames(container)
    for clone_full in clone_names:
        if not IsContainerCreated(clone_full):
            return False
    return True

# Check to see if my_container_name container has been created or not
def IsContainerCreated(mycontainer_name):
    retval = True
    command = "docker inspect -f {{.Created}} --type container %s" % mycontainer_name
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(shlex.split(command), stderr=subprocess.PIPE, stdout=subprocess.PIPE)
    if result == FAILURE:
       retval = False
    logger.DEBUG("Result of subprocess.call for %s IsContainerCreated is %s (1=>FAILURE)" % (mycontainer_name, result))
    return retval

def GetNetParam(start_config, mysubnet_name, mysubnet_ip, mycontainer_name):
    ''' return the network address parameter and mac parameter for use in creating a container
        or connecting the container to a network.  Parse out mac address suffix if it exists,
        and adjust the ip address based on clone numbers if the address has a "+CLONE" suffix '''
    mac = ''
    ip_param = ''
    if ':' in mysubnet_ip:
        mysubnet_ip, mac_addr = mysubnet_ip.split(':',1)
        mac = '--mac-address=%s' % mac_addr 
    elif mysubnet_ip.lower() == 'auto_mac':
        mac_addr = get_new_mac(start_config.subnets[mysubnet_name].macvlan_use)
        mac = '--mac-address=%s' % mac_addr
    if not mysubnet_ip.lower().startswith('auto'):
        if '+' in mysubnet_ip:
            ip, clone_type = mysubnet_ip.split('+')
            if clone_type.lower() == 'clone' or start_config.multi_user == 'clones':
                name, role = mycontainer_name.rsplit('.',1)
                dumb, offset = name.rsplit('-', 1)
                try:
                    offset_int = int(offset) 
                except:
                    logger.ERROR('expected use of clone, but did not find clone counter in %s' % mycontainer_name)
                    exit(1)
                ip_start, ip_suffix = ip.rsplit('.', 1)
                ip_suffix_int = int(ip_suffix)
                new_suffix = ip_suffix_int + offset_int - 1
                if new_suffix > 254:
                    logger.ERROR('IP address adjusted to invalid value %d %s' % (new_suffix, mysubnet_ip))
                    exit(1)
                ip_param = '--ip=%s.%d' % (ip_start, new_suffix)
            elif clone_type.lower() == 'clone_mac' and start_config.multi_user == 'client':
                # assuming we are a multiuser client
                mac_addr = get_new_mac(start_config.subnets[mysubnet_name].macvlan_use)
                mac = '--mac-address=%s' % mac_addr

            else:
                print('ip %s' % ip)
                ip_param = '--ip=%s' % ip
                
        else:
            ip_param = '--ip=%s' % mysubnet_ip
    return ip_param, mac

def ConnectNetworkToContainer(start_config, mycontainer_name, mysubnet_name, mysubnet_ip):
    logger.DEBUG("Connecting more network subnet to container %s" % mycontainer_name)
    ip_param, dumb = GetNetParam(start_config, mysubnet_name, mysubnet_ip, mycontainer_name)
    command = "docker network connect %s %s %s" % (ip_param, mysubnet_name, mycontainer_name)
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(shlex.split(command), stderr=subprocess.PIPE, stdout=subprocess.PIPE)
    logger.DEBUG("Result of subprocess.call ConnectNetworkToContainer is %s" % result)
    return result

def DisconnectNetworkFromContainer(mycontainer_name, mysubnet_name):
    logger.DEBUG("Disconnecting more network subnet to container %s" % mycontainer_name)
    command = "docker network disconnect %s %s" % (mysubnet_name, mycontainer_name)
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    result = 0
    if len(output[1]) > 0:
        logger.ERROR(output[1])
        result = 1;
    return result

def SetXhost():
    ''' allow container root users to access xserver '''
    cmd = 'xhost'
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if not 'LOCAL:' in output[0]:
        cmd = 'xhost local:root'
        os.system(cmd)
    
def GetContainerCloneNames(container):    
    ''' populate dictionary with hostname/container names based on the quantity of clones
        that are to be created '''
    retval = {}
    if container.clone_copies is None or container.clone == 1:
        retval[container.full_name] = container.hostname
    else:
        try:
            count = int(container.clone_copies)
        except:
            logger.ERROR('bad clone value for %s' % container.hostname)
            exit(1)
        name, role = container.full_name.rsplit('.', 1)
        for i in range(1, count+1):
            hostname = '%s-%d' % (container.hostname, i)
            fullname = '%s-%d.%s' % (name, i, role)
            retval[fullname] = hostname
    return retval
       
def GetDNS(): 
    dns_param = ''
    dns_param = '--dns=8.8.8.8'
    cmd="nmcli dev show | grep 'IP4.DNS'"
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0]) > 0: 
        for line in output[0].splitlines(True):
            dns_param = '--dns=%s %s' % (line.split()[1].strip(), dns_param)
            ''' just take first '''
            break
    return dns_param

def GetX11SSH():
    ''' EXPERIMENTAL, not used '''
    ip = '192.168.1.222'
    xauth = '/tmp/.docker.xauth'
    #display = os.getenv('DISPLAY') 
    display = ':10'
    cmd = 'xauth list %s' % display
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0]) > 0: 
        parts = output[0].strip().split()
        magic_cookie = parts[2]
    else:
        print('could not find magic cookie')
        exit(1)
    x11_port = display.split(':')[1] 
    #print('x11_port %s' % x11_port)
    cmd = 'xauth -f /tmp/.docker.xauth add %s:%s . %s' % (ip, x11_port, magic_cookie)
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    os.chmod(xauth, 0777)
    retval = '--env="%s:%s" -v %s:%s -e XAUTHORITY="%s"' % (ip, x11_port, xauth, xauth, xauth)
    #retval = '--env="DISPLAY" -v %s:%s -e XAUTHORITY="%s"' % (xauth, xauth, xauth)
    return retval 

def isUbuntuSystemd(image_name):
    done = False
    retval = False
    #print('check if %s is systemd' % image_name)
    cmd = "docker inspect -f '{{json .Config.Labels.base}}' --type image %s" % image_name
    #print('lab container cmd is %s' % cmd)
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
            logger.DEBUG('base %s' % output[0])
            if output[0].strip() == 'null': 
                base = image_name
            else:
                base = output[0].rsplit('.', 1)[0]+'"'
            cmd = "docker history --no-trunc %s" % base
            ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
            output = ps.communicate()
            for line in output[0].splitlines():
                if 'Labtainer base image from ubuntu-systemd' in line:
                    retval = True
                    break

    return retval

def CreateSingleContainer(labtainer_config, start_config, container, mysubnet_name=None, mysubnet_ip=None, quiet=False):
    ''' create a single container -- or all clones of that container per the start.config '''
    logger.DEBUG("Create Single Container for %s" % container.name)
    retval = True
    #image_exists, result, new_image_name = ImageExists(container.image_name, container.registry)
    image_info = imageInfo(container.image_name, container.registry, labtainer_config, quiet=quiet)
    start_script = container.script     
    if image_info is None:
        logger.ERROR('Could not find image for %s' % container.image_name)
        retval = False
    else:
        new_image_name = container.image_name
        if not image_info.local_build:
            new_image_name = '%s/%s' % (container.registry, container.image_name) 
        if not image_info.local:
            dockerPull(container.registry, container.image_name)
        docker0_IPAddr = getDocker0IPAddr()
        logger.DEBUG("getDockerIPAddr result (%s)" % docker0_IPAddr)
        volume=''
        ubuntu_systemd = isUbuntuSystemd(new_image_name)
        if container.script == '' or ubuntu_systemd:
            logger.DEBUG('Container %s is systemd' % (new_image_name))
            ''' a systemd container, centos or ubuntu? '''
            if ubuntu_systemd:
                start_script = ''
                #volume='--security-opt seccomp=confined --tmpfs /run --tmpfs /run/lock -v /sys/fs/cgroup:/sys/fs/cgroup:ro'
                volume='--security-opt seccomp=unconfined --tmpfs /run --tmpfs /run/lock -v /sys/fs/cgroup:/sys/fs/cgroup:ro'
                cmd = 'docker run --rm --privileged -v /:/host %s setup' % new_image_name
                logger.DEBUG('cmd is %s' % cmd)
                ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
                output = ps.communicate()
                logger.DEBUG('back from docker run, output %s' % (output[0]))
                if len(output[1]) > 0:
                    logger.DEBUG('back from docker run, error %s' % (output[1]))
                
            else:
                volume='-v /sys/fs/cgroup:/sys/fs/cgroup:ro'
        if container.x11.lower() == 'yes':
            #volume = '-e DISPLAY -v /tmp/.Xll-unix:/tmp/.X11-unix --net=host -v$HOME/.Xauthority:/home/developer/.Xauthority'
            volume = volume+' --env="DISPLAY"  --volume="/tmp/.X11-unix:/tmp/.X11-unix:rw"'
            logger.DEBUG('container using X11')
        add_hosts = ''     
        logger.DEBUG('dumb debug message')
        for item in container.add_hosts:
            if ':' not in item:
               if item in start_config.lan_hosts:
                   for entry in start_config.lan_hosts[item]:
                       add_this = '--add-host %s ' % entry
                       add_hosts += add_this
               else:
                   logger.ERROR('ADD-HOST entry in start.config missing colon: %s' % item)
                   logger.ERROR('sytax: ADD-HOST <host>:<ip>')
                   return
            else:
               add_this = '--add-host %s ' % item
               add_hosts += add_this
        add_host_param = '--add-host my_host:%s %s' % (docker0_IPAddr, add_hosts)
        dns_param = GetDNS()
        priv_param = ''
        if container.no_privilege != 'yes':
            priv_param = '--privileged'

        mac = ''
        subnet_ip = ''
        network_param = ''
        if mysubnet_name is not None:
            network_param = '--network=%s' % mysubnet_name

        multi_user = ''
        if container.client == 'yes' and start_config.multi_user is not None:
            #print('use putenv to set %s' % start_config.multi_user)
            os.putenv("DISTRIBUTED_LABTAINER", start_config.multi_user)
            ''' why does putenv not set the value? '''
            os.environ['DISTRIBUTED_LABTAINER'] = start_config.multi_user
            multi_user = '--env=DISTRIBUTED_LABTAINER' 


        clone_names = GetContainerCloneNames(container)
        for clone_fullname in clone_names:
            clone_host = clone_names[clone_fullname]
            if mysubnet_name is not None:
                subnet_ip, mac = GetNetParam(start_config, mysubnet_name, mysubnet_ip, clone_fullname)
            #createsinglecommand = "docker create -t %s --ipc host --cap-add NET_ADMIN %s %s %s %s %s --name=%s --hostname %s %s %s %s %s" % (dns_param, 
            createsinglecommand = "docker create -t %s --cap-add NET_ADMIN %s %s %s %s %s --name=%s --hostname %s %s %s %s %s" % (dns_param, 
                    network_param, subnet_ip, mac, priv_param, add_host_param,  clone_fullname, clone_host, volume, 
                    multi_user, new_image_name, start_script)
            logger.DEBUG("Command to execute was (%s)" % createsinglecommand)
            ps = subprocess.Popen(shlex.split(createsinglecommand), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
            output = ps.communicate()
            if len(output[1]) > 0:
                logger.DEBUG('command was %s' % createsinglecommand)
                if 'Cannot connect to the Docker daemon' in output[1]:
                    print('\n\nERROR: Docker seems not to be running.')
                    print('Try "sudo systemctl restart docker"\n\n')
                logger.ERROR('CreateSingleContainer %s' % output[1])
                retval = False
                break
            #print('result of create %s' % output[0])

    return retval

def GetIface(ip):
    cmd = 'ifconfig | grep -B1 "inet addr:%s" | awk \'$1!="inet" && $1!="--" {print $1}\'' % ip
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    return output[0].strip()

def CheckPromisc(iface):
    cmd = "netstat -i | grep enp0s8 | awk '{print $12}'"
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if 'P' in output[0]:
        return True
    else:
        return False

# Create SUBNETS
def CreateSubnets(start_config):
    subnets = start_config.subnets
    #for (subnet_name, subnet_network_mask) in networklist.iteritems():
    for subnet_name in subnets:
        subnet_network_mask = subnets[subnet_name].mask
        logger.DEBUG("subnet_name is %s" % subnet_name)
        logger.DEBUG("subnet_network_mask is %s" % subnet_network_mask)

        command = "docker network inspect %s" % subnet_name
        logger.DEBUG("Command to execute is (%s)" % command)
        inspect_result = subprocess.call(shlex.split(command), stderr=subprocess.PIPE, stdout=subprocess.PIPE)
        logger.DEBUG("Result of subprocess.call CreateSubnets docker network inspect is %s" % inspect_result)
        if inspect_result == FAILURE:
            # Fail means does not exist - then we can create
            macvlan = ''
            ip_range = ''
            net_type = 'bridge'
            if subnets[subnet_name].macvlan_use is not None:
                #iface = GetIface(subnets[subnet_name].macvlan)
                iface = subnets[subnet_name].macvlan_use
                if iface is None or len(iface) == 0:
                    logger.ERROR("No IP assigned to network %s, assign an ip on Linux host to enable use of macvlan with Labtainers")
                    exit(1)
                if not CheckPromisc(iface):
                    logger.WARNING("network %s not in promisc mode, required for macvlan inter-vbox comms\nUse: sudo ifconfig %s promisc" % (iface, iface))
                macvlan = '-o parent=%s -o macvlan_mod=bridge' % iface
                net_type = 'macvlan'
            if subnets[subnet_name].ip_range is not None:
                ip_range = '--ip-range %s' % subnets[subnet_name].ip_range 
            if subnets[subnet_name].gateway != None:
                logger.DEBUG(subnets[subnet_name].gateway)
                subnet_gateway = subnets[subnet_name].gateway
                command = "docker network create -d %s --gateway=%s --subnet %s %s %s %s" % (net_type, subnet_gateway, subnet_network_mask, macvlan, ip_range, subnet_name)
            else:
                command = "docker network create -d %s --subnet %s %s %s %s" % (net_type, subnet_network_mask, macvlan, ip_range, subnet_name)
            logger.DEBUG("Command to execute is (%s)" % command)
            #create_result = subprocess.call(command, shell=True)
            ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
            output = ps.communicate()
            logger.DEBUG("stdout of subprocess.call CreateSubnets docker network create is %s" % output[0])
            if len(output[1]) > 0:
                logger.DEBUG('stderr of %s is %s' % (command, output[1]))
                found_match_network = False
                found_match_network_name = ""
                # Before a hard exit - give the user some indication of what to do next
                # First check to see if a gateway is provided and it is already used
                if subnets[subnet_name].gateway != None:
                    found_match_network, found_match_network_name = FindNetworkGivenGatewayIP(subnets[subnet_name].gateway)
                    # If Gateway IP address not okay, no need to check subnet anymore
                    if not found_match_network:
                        # Gateway IP address might be okay but subnet mask might not
                        found_match_network, found_match_network_name = FindNetworkGivenSubnet(subnet_network_mask)
                else:
                    # No Gateway IP address, check the subnet mask only
                    found_match_network, found_match_network_name = FindNetworkGivenSubnet(subnet_network_mask)

                # At this point, if still not found then just print error and exit
                if not found_match_network:
                    logger.ERROR("Failed to create %s subnet at %s, %s\n" % (subnet_name, subnet_network_mask, output[1]))
                    logger.ERROR("command was %s\n" % command)
                    sys.exit(1)
                else:
                    # Found either a network matching the Gateway IP address or matching subnet
                    lablist = []
                    # See if any lab is using that network
                    lablist = GetListLabContainerOnNetwork(found_match_network_name)
                    if lablist == []:
                        # No lab is using the network - tell user to remove that "left-over" network
                        logger.ERROR("An existing Docker network is preventing this lab from starting.")
                        logger.ERROR("Try removing the network with:")
                        logger.ERROR("docker network rm %s" % found_match_network_name)
                        sys.exit(1)
                    else:
                        # There is lab using that network - tell user to stop that lab first
                        logger.ERROR("An existing Docker network is preventing this lab from starting.")
                        logger.ERROR("This may be due to a failure to stop a previous lab.")
                        logger.ERROR("Please stop the lab %s and try again." % lablist)
                        sys.exit(1)
        else:
            logger.WARNING("Already exists! Not creating %s subnet at %s!\n" % (subnet_name, subnet_network_mask))

def RemoveSubnets(subnets, ignore_stop_error):
    for subnet_name in subnets:
        command = "docker network rm %s" % subnet_name
        ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        output = ps.communicate()
        if len(output[1]) > 0:
            if ignore_stop_error:
                logger.DEBUG('Encountered error removing subnet %s' % subnet_name)
            else:
                logger.ERROR('Encountered error removing subnet %s' % subnet_name)

EMAIL_TMP='./.tmp/email.txt' 
def getLastEmail():
    retval = None
    if os.path.isfile(EMAIL_TMP):
        with open(EMAIL_TMP) as fh:
            retval = fh.read()
            if retval is not None:
                retval = retval.strip()
    return retval

def putLastEmail(email):
    try:
        os.mkdir('./.tmp')
    except:
        pass
    with open(EMAIL_TMP, 'w') as fh:
            fh.write(email)

def GetLabSeed(lab_master_seed, student_email):
    # Create hash using LAB_MASTER_SEED concatenated with user's e-mail
    # LAB_MASTER_SEED is per laboratory - specified in start.config
    string_to_be_hashed = '%s:%s' % (lab_master_seed, student_email)
    mymd5 = md5.new()
    mymd5.update(string_to_be_hashed)
    mymd5_hex_string = mymd5.hexdigest()
    return mymd5_hex_string

#def ParamStartConfig(lab_seed):
    
def ParamForStudent(lab_master_seed, mycontainer_name, container_user, container_password, labname, student_email, lab_path, name, image_info):
    mymd5_hex_string = GetLabSeed(lab_master_seed, student_email)
    logger.DEBUG(mymd5_hex_string)

    if not ParameterizeMyContainer(mycontainer_name, container_user, container_password, mymd5_hex_string,
                                                          student_email, labname, lab_path, name, image_info):
        logger.ERROR("Failed to parameterize lab container %s!\n" % mycontainer_name)
        sys.exit(1)
    logger.DEBUG('back from ParameterizeMyContainer for %s' % mycontainer_name)

def DockerCmd(cmd, noloop=False):
    ok = False
    count = 0
    if noloop:
        count = 1000
    while not ok:
        logger.DEBUG("Command to execute is (%s)" % cmd)
        ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        output = ps.communicate()
        if len(output[1]) > 0:
            count += 1
            logger.DEBUG("Failed cmd %s %s" % (cmd, output[1]))
            if count > 1:
                return False
            time.sleep(1)
        else:
           ok = True
        if len(output[0]) > 0:
            logger.DEBUG("cmd %s stdout: %s" % (cmd, output[0]))
    return True


def CopyInstrConfig(mycontainer_name, container_user, lab_path):
    cmd = 'docker cp %s/instr_config/.  %s:/home/%s/.local/instr_config/' % (lab_path, mycontainer_name, container_user)
    if not DockerCmd(cmd):
        logger.ERROR('failed %s' % cmd)
        exit(1)
    cmd = 'docker cp %s/config/.  %s:/home/%s/.local/config/' % (lab_path, mycontainer_name, container_user)
    if not DockerCmd(cmd):
        logger.ERROR('failed %s' % cmd)
        exit(1)


def CopyLabBin(mycontainer_name, container_user, lab_path, name, image_info):
    cmd = 'docker cp lab_bin/.  %s:/home/%s/.local/bin/' % (mycontainer_name, container_user)
    if not DockerCmd(cmd):
        logger.ERROR('failed %s' % cmd)
        exit(1)

    ''' TBD DO NOT move lab/config here -- would not catch the tar_list.txt files (skip list) '''
    ''' TBD perhaps move lab/_bin to here?  would it save duplicate containers?'''
    #container_bin = os.path.join(lab_path, name,'_bin')
    #if os.path.isdir(container_bin):
    #    cmd = 'docker cp %s/.  %s:/home/%s/.local/bin/' % (container_bin, mycontainer_name, container_user)
    #    DockerCmd(cmd)
    tmp_dir=os.path.join('/tmp/labtainers', mycontainer_name)
    shutil.rmtree(tmp_dir, ignore_errors=True)
    try:
        os.makedirs(tmp_dir)
    except os.error:
        logger.ERROR("did not expect to find dir %s" % tmp_dir)
 
    if not os.path.isfile('./lab_sys/sbin/capinout'):
        print('\n\n********* ERROR ***********')
        print('./lab_sys/sbin/capinout is missing.  If this is a development system, you may need to')
        print('go to the tool-src/capinout directory and run ./mkit.sh')
        
    dest_tar = os.path.join(tmp_dir, 'labsys.tar')
    cmd = 'tar cf %s -C ./lab_sys sbin lib &>/dev/null' % dest_tar
    os.system(cmd)

    cmd = 'docker cp %s %s:/var/tmp/' % (dest_tar, mycontainer_name)
    if not DockerCmd(cmd):
        logger.ERROR('failed %s' % cmd)
        exit(1)

    cmd = 'docker exec %s script -q -c "sudo tar -x --keep-directory-symlink -f /var/tmp/labsys.tar -C /"' % (mycontainer_name)
    if not DockerCmd(cmd):
        cmd = 'docker cp lab_sys/.  %s:/' % (mycontainer_name)
        if not DockerCmd(cmd):
            logger.ERROR('failed %s' % cmd)
            exit(1)

# Copy Students' Artifacts from host to instructor's lab container
def CopyStudentArtifacts(labtainer_config, mycontainer_name, labname, container_user, container_password):
    # Set the lab name 
    command = 'docker exec %s script -q -c "echo %s > /home/%s/.local/.labname" /dev/null' % (mycontainer_name, labname, container_user)
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(command, shell=True)
    logger.DEBUG("Result of subprocess.call CopyStudentArtifacts set labname is %s (1=>FAILURE)" % result)
    if result == FAILURE:
        logger.ERROR("Failed to set labname in container %s!\n" % mycontainer_name)
        sys.exit(1)

    # Create is_grade_container
    command = 'docker exec %s script -q -c "echo TRUE > /home/%s/.local/.is_grade_container" /dev/null' % (mycontainer_name, container_user)
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(command, shell=True)
    logger.DEBUG("Result of subprocess.call CopyStudentArtifacts create is_grade_container is %s (1=>FAILURE)" % result)
    if result == FAILURE:
        logger.ERROR("Failed to create is_grade_container in container %s!\n" % mycontainer_name)
        sys.exit(1)

    username = getpass.getuser()
    xfer_dir = os.path.join(labtainer_config.host_home_xfer, labname)
    zip_filelist = glob.glob('/home/%s/%s/*.zip' % (username, xfer_dir))
    logger.DEBUG("filenames is (%s)" % zip_filelist)
    # Copy zip files from 'Shared' folder to 'home/$CONTAINER_USER'
    for fname in zip_filelist:
        logger.DEBUG("name is %s" % fname)
        base_fname = os.path.basename(fname)
        # Copy zip file 
        command = 'docker cp %s %s:/home/%s/' % (fname, mycontainer_name, container_user)
        logger.DEBUG("Command to execute is (%s)" % command)
        result = subprocess.call(shlex.split(command))
        logger.DEBUG("Result of subprocess.call CopyStudentArtifacts copy zipfile (%s) is %s (1=>FAILURE)" % (fname, result))
        if result == FAILURE:
            logger.ERROR("Failed to copy student artifacts into container %s!\n" % mycontainer_name)
            sys.exit(1)
        #command = 'docker exec %s echo "%s\n" | sudo -S chown %s:%s /home/%s/%s' % (mycontainer_name, container_password, 
        #             container_user, container_user, container_user, base_fname)
        #command = 'docker exec %s chown %s:%s /home/%s/%s' % (mycontainer_name, 
        #             container_user, container_user, container_user, base_fname)
        #logger.DEBUG("Command to execute is (%s)" % command)
        #result = subprocess.call(command, shell=True)
        #logger.DEBUG("Result of subprocess.call CopyStudentArtifacts copy zipfile (%s) is %s" % (fname, result))
        #if result == FAILURE:
        #    logger.ERROR("Failed to set labname in container %s!\n" % mycontainer_name)
        #    sys.exit(1)

def GetRunningContainersList():
    cmd = "docker container ls --format {{.Names}}"
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        logger.DEBUG('No running containers: error returned %s, return false' % output[1])
        return False, None
    result = output[0].strip()
    logger.DEBUG('result is %s' % result)
    if 'Error:' in result or len(result.strip()) == 0:
        if 'Error:' in result:
            logger.DEBUG("Command was (%s)" % cmd)
            logger.DEBUG("Error from command = '%s'" % result)
        return False, result
    containers_list = result.split('\n')
    return True, containers_list

def GetRunningLabNames(containers_list):
    labnameslist = []
    found_lab_role = False
    for each_container in containers_list:
        #print each_container
        if each_container.endswith('.student'):
            splitstring = each_container.split('.')
            labname = splitstring[0]
            found_lab_role = True
            if labname not in labnameslist:
                labnameslist.append(labname)
    return found_lab_role, labnameslist

class ImageInfo():
    def __init__(self, name, creation, user, local, local_build, version, use_tag):
        self.name = name
        self.creation = creation
        self.user = user
        self.local = local
        ''' whether a locally built image '''
        self.local_build = local_build  
        self.version =  None
        self.use_tag = use_tag
        if version is not None:
            version = version.replace('"', '')
            if version != 'null' and len(version.strip()) > 0:
                try:
                    self.version = version
                except:
                    logger.ERROR('failed getting version from string <%s>' % version)
                    traceback.print_exc()
                    traceback.print_stack()
                    exit(1)

def inspectImage(image_name):
    created = None
    user = None
    version = None
    cmd = "docker inspect -f '{{.Created}}' --type image %s" % image_name
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        created = output[0].strip()
    cmd = "docker inspect -f '{{.Config.User}}' --type image %s" % image_name
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        user = output[0].strip()
    cmd = "docker inspect --format='{{json .Config.Labels.version}}' --type image %s" % image_name
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[0].strip()) > 0:
        version = output[0].strip()
    return created, user, version

def imageInfo(image_name, registry, labtainer_config, is_rebuild=False, no_pull=False, quiet=False):
    ''' image_name lacks registry info (always) 
        First look if plain image name exists, suggesting
        an ongoing build/test situation '''    
    retval = None
    use_tag = 'latest'
    created, user, version = inspectImage(image_name)
    if created is not None:
        retval = ImageInfo(image_name, created, user, True, True, version, use_tag) 
        logger.DEBUG('%s local built, ts %s %s' % (image_name, created, user)) 
    else:
        ''' next see if there is a local image from the desired registry '''
        with_registry = '%s/%s' % (registry, image_name)
        created, user, version = inspectImage(with_registry)
        if created is not None:
            retval = ImageInfo(with_registry, created, user, True, False, version, use_tag) 
            logger.DEBUG('%s local from reg, ts %s %s version: %s' % (with_registry, created, user, version)) 
        elif not no_pull:
            ''' See if the image exists in the desired registry '''
            if registry == labtainer_config.test_registry:
                created, user, version, use_tag, base = InspectLocalReg.inspectLocal(image_name, registry, is_rebuild, quiet)
            else:
                created, user, version, use_tag = InspectRemoteReg.inspectRemote(with_registry, is_rebuild, quiet)
            if created is not None:
                logger.DEBUG('%s only on registry %s, ts %s %s version %s use_tag %s' % (with_registry, registry, created, user, version, use_tag)) 
                retval = ImageInfo(with_registry, created, user, False, False, version, use_tag)
    if retval is None:
        logger.DEBUG('%s not found anywhere' % image_name)

    return retval

def ImageExists(image_name, registry):
    '''
    determine if a given image exists.
    '''
    retval = True
    logger.DEBUG('check existence of image %s registry %s' % (image_name, registry))
    cmd = "docker inspect -f '{{.Created}}' --type image %s" % image_name
    ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        if registry is not None:
            with_registry = '%s/%s' % (registry, image_name)
            return ImageExists(with_registry, None)
        else:
            logger.DEBUG('No image: error returned %s, return false' % output[1])
            return False, None, None
    result = output[0].strip()
    #logger.DEBUG('result is %s' % result)
    if 'Error:' in result or len(result.strip()) == 0:
        if registry is not None:
            with_registry = '%s/%s' % (image_name, registry)
            return ImageExists(with_registry, None)
        else:
            if 'Error:' in result:
                logger.DEBUG("Command was (%s)" % cmd)
                logger.DEBUG("Error from command = '%s'" % result)
            return False, result, image_name
    return True, result, image_name

def GetBothConfigs(lab_path, logger, servers=None, clone_count=None):
    labtainer_config_dir = os.path.join(os.path.dirname(os.path.dirname(lab_path)), 'config', 'labtainer.config')
    labtainer_config = ParseLabtainerConfig.ParseLabtainerConfig(labtainer_config_dir, logger)
    labname = os.path.basename(lab_path)
    config_path       = os.path.join(lab_path,"config") 
    start_config_path = os.path.join(config_path,"start.config")
    start_config = ParseStartConfig.ParseStartConfig(start_config_path, labname, 
                       labtainer_config, logger, servers=servers, clone_count=clone_count)
    return labtainer_config, start_config

def RebuildLab(lab_path, force_build=False, quiet_start=False, 
               just_container=None, run_container=None, servers=None, clone_count=None, no_pull=False):
    # Pass 'True' to ignore_stop_error (i.e., ignore certain error encountered during StopLab
    #                                         since it might not even be an error)
    StopLab(lab_path, True, run_container=run_container, servers=servers, clone_count=clone_count)
    logger.DEBUG('Back from StopLab clone_count was %s' % clone_count)
    labname = os.path.basename(lab_path)
    my_start_config = os.path.join('./.tmp',labname, 'start.config')
    if os.path.isfile(my_start_config):
        logger.DEBUG('Cached start.config removed %s' % my_start_config)
        os.remove(my_start_config)
    labtainer_config, start_config = GetBothConfigs(lab_path, logger, servers, clone_count)
    
    DoRebuildLab(lab_path, force_build=force_build, 
                 just_container=just_container, start_config = start_config, 
                 labtainer_config = labtainer_config, run_container=run_container, 
                 servers=servers, clone_count=clone_count, no_pull=no_pull)

    # Check existence of /home/$USER/$HOST_HOME_XFER directory - create if necessary
    host_home_xfer = labtainer_config.host_home_xfer
    myhomedir = os.environ['HOME']
    host_xfer_dir = '%s/%s' % (myhomedir, host_home_xfer)
    CreateHostHomeXfer(host_xfer_dir)
    DoStart(start_config, labtainer_config, lab_path, quiet_start, 
            run_container, servers, clone_count)

def dockerPull(registry, image_name):
    cmd = 'docker pull %s/%s' % (registry, image_name)
    logger.DEBUG('%s' % cmd)
    print('pulling %s from %s' % (image_name, registry))
    ps = subprocess.Popen(shlex.split(cmd), stderr=subprocess.PIPE, stdout=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1]) > 0:
        return False
    print('Done with pull')
    return True

def DoRebuildLab(lab_path, force_build=False, just_container=None, 
                 start_config=None, labtainer_config=None, run_container=None, servers=None, 
                 clone_count=None, no_pull=False):
    retval = set()
    labname = os.path.basename(lab_path)
    is_valid_lab(lab_path)
    if start_config is None:
        labtainer_config, start_config = GetBothConfigs(lab_path, logger, servers, clone_count)
    host_home_xfer = labtainer_config.host_home_xfer

    build_student = 'bin/buildImage.sh'
    build_instructor = 'bin/buildInstructorImage.sh'
    LABS_DIR = os.path.abspath('../../labs')
    didfix = False
    ''' hackey assumption about running from labtainers-student or labtainers-instructor '''
    container_bin = './lab_bin'
    for name, container in start_config.containers.items():
        logger.DEBUG('this container name %s just_container %s' % (name, just_container))
        if just_container is not None and just_container != name:
            continue
        elif just_container == name:
            force_build = True
            print('Force build of %s' % just_container)
        mycontainer_name       = container.full_name
        mycontainer_image_name = container.image_name
        retval.add(container.registry)

        clone_names = GetContainerCloneNames(container)
        for clone_full in clone_names:
            cmd = 'docker rm %s' % clone_full
            ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
            output = ps.communicate()
            #logger.DEBUG("Command was (%s)" % cmd)
            if len(output[1]) > 0:
                logger.DEBUG("Error from command %s was  '%s'" % (cmd, output[1]))

        if container.from_image is not None:
            logger.DEBUG('skip image taken from %s' % container.from_image)
            continue

        force_this_build = force_build
        logger.DEBUG('force_this_build: %r' % force_this_build)
        image_info = imageInfo(mycontainer_image_name, container.registry, labtainer_config, is_rebuild=True, no_pull=no_pull)
        if not force_this_build and image_info is None:
            logger.DEBUG('image exists nowhere, so force the build')
            force_this_build = True
        container_dir = os.path.join(lab_path, name)
        try:
            os.mkdir(os.path.join(container_dir, 'home_tar'))
        except:
            pass
        try:
            os.mkdir(os.path.join(container_dir, 'sys_tar'))
        except:
            pass
        ''' make sure big files have been copied before checking tars '''
        BigFiles.BigFiles(lab_path)
        BigExternal.BigExternal(lab_path)
        ''' create sys_tar and home_tar before checking build dependencies '''
        CheckTars.CheckTars(container_dir, name, logger)
        if force_this_build or CheckBuild(lab_path, mycontainer_image_name, image_info, mycontainer_name, name, True, container_bin, start_config, container.registry, container.user):
            logger.DEBUG("Will rebuild %s,  force_this_build: %s" % (mycontainer_name, force_this_build))
            if os.path.isfile(build_student):
                cmd = '%s %s %s %s %s %s %s %s %s %s %s' % (build_student, labname, name, container.user, 
                      container.password, True, LABS_DIR, labtainer_config.apt_source, container.registry, framework_version, str(no_pull))
            elif os.path.isfile(build_instructor):
                cmd = '%s %s %s %s %s %s %s %s %s %s %s' % (build_instructor, labname, name, container.user, 
                      container.password, True, LABS_DIR, labtainer_config.apt_source, container.registry, framework_version, str(no_pull))
            else:
                logger.ERROR("no image rebuild script\n")
                exit(1)
            logger.DEBUG('cmd is %s' % cmd)     
            ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
            fatal_error = False
            while True:
                line = ps.stdout.readline()
                if line != '':
                    #if 'Error in docker build result 1' in line:
                    if 'Error in docker build result 1' in line or 'Error in docker build result 2' in line \
                       or 'syntax error' in line:
                        logger.ERROR(line)
                        fatal_error = True
                    else:
                        logger.DEBUG(line)
                else:
                    break
            while True:
                line = ps.stderr.readline()
                if line != '':
                    if 'Error in docker build result 1' in line or 'Error in docker build result 2' in line \
                       or 'syntax error' in line:
                        logger.ERROR(line)
                        fatal_error = True
                    else:
                        logger.DEBUG(line)
                else:
                    break
            if fatal_error:
                exit(1)
            #if os.system(cmd) != 0:
            #    logger.ERROR("build of image failed\n")
            #    logger.DEBUG('cmd was %s' % cmd)
            #    exit(1)
    return retval

def defineAdditionalIP(container_name, post_start_if, post_start_nets):
    for subnet in post_start_nets:
        existing_ip = post_start_if[subnet]
        cmd = "docker exec %s bash -c 'ifconfig'" % (container_name)
        logger.DEBUG('cmd is %s' % cmd)
        ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        output = ps.communicate()
        logger.DEBUG('out0 %s \nout1 %s' % (output[0], output[1]))
        current_if = None
        this_if = None
        for line in output[0].splitlines():
            parts = line.split()
            if len(parts) < 2:
                continue
            if parts[1] == 'Link':
                current_if = parts[0]
            elif parts[1] == ('addr:%s' % post_start_if[subnet]):
                this_if = current_if
                break
        count = 1
        for ip in post_start_nets[subnet]:
            cmd = "docker exec %s bash -c 'ifconfig %s:%d %s'" % (container_name, this_if, count, ip)
            logger.DEBUG('next cmd is %s' % cmd)
            if not DockerCmd(cmd):
                print('error doing %s' % cmd)
                exit(1) 
            count += 1
     
        
    
def DoStartOne(labname, name, container, start_config, labtainer_config, lab_path,  
               student_email, quiet_start, results, auto_grade, image_info):
        retval = True
        mycontainer_name       = container.full_name
        mycontainer_image_name = container.image_name
        container_user         = container.user
        container_password         = container.password
        container_hostname         = container.hostname
        ''' mananage interfaces with multiple IP addresses, docker does not support directly '''
        post_start_if = {}
        post_start_nets = {}

        haveContainer = AllContainersCreated(container)
        logger.DEBUG("DoStart for %s AllContainersCreated result (%s)" % (container.name, haveContainer))

        # Set need_seeds=False first
        need_seeds=False
        # IsContainerCreated return False if container does not exists
        if not haveContainer:
            # Container does not exist, create the container
            # Use CreateSingleContainer()
            containerCreated = False
            if len(container.container_nets) == 0:
                containerCreated = CreateSingleContainer(labtainer_config, start_config, container, quiet=quiet_start)
            else:
                mysubnet_name, mysubnet_ip = container.container_nets.popitem()
                subnet_name = mysubnet_name
                if ':' in mysubnet_name:
                    subnet_name = mysubnet_name.split(':')[0] 
                    post_start_if[subnet_name] = mysubnet_ip
                containerCreated = CreateSingleContainer(labtainer_config, start_config, container, subnet_name, mysubnet_ip, quiet=quiet_start)
                
            logger.DEBUG("CreateSingleContainer result (%s)" % containerCreated)
            if not containerCreated:
                logger.ERROR("CreateSingleContainer fails to create container %s!\n" % mycontainer_name)
                results.append(False)
                return

            # Give the container some time -- just in case
            #time.sleep(3)
            # If we just create it, then set need_seeds=True
            need_seeds=True

        # Check again - 
        haveContainer = AllContainersCreated(container)
        logger.DEBUG("AllContainersCreated second check for %s result (%s)" % (container.name, haveContainer))

        # IsContainerCreated returned False if container does not exists
        if not haveContainer:
            logger.ERROR("Container %s still not created!\n" % mycontainer_name)
            results.append(False)
            return
       
        clone_names = GetContainerCloneNames(container)
        for mycontainer_name in clone_names:
            for mysubnet_name, mysubnet_ip in container.container_nets.items():
                subnet_name = mysubnet_name
                if ':' in mysubnet_name:
                    subnet_name = mysubnet_name.split(':')[0] 
                    if subnet_name not in post_start_nets:
                        post_start_nets[subnet_name] = []
                    if subnet_name not in post_start_if:
                        post_start_if[subnet_name] = mysubnet_ip
                        logger.DEBUG('container: %s assigned post_start_if[%s] %s, connecting' % (mycontainer_name, subnet_name, mysubnet_ip))
                        connectNetworkResult = ConnectNetworkToContainer(start_config, mycontainer_name, subnet_name, mysubnet_ip)
                    else:
                        post_start_nets[subnet_name].append(mysubnet_ip)
                else:
                    connectNetworkResult = ConnectNetworkToContainer(start_config, mycontainer_name, mysubnet_name, mysubnet_ip)

            # Start the container
            if not StartMyContainer(mycontainer_name):
                logger.ERROR("Container %s failed to start!\n" % mycontainer_name)
                results.append(False)
                return
            defineAdditionalIP(mycontainer_name, post_start_if, post_start_nets)

            clone_need_seeds = need_seeds
            if not clone_need_seeds:
                cmd = "docker exec %s bash -c 'ls -l /var/labtainer/did_param'" % (mycontainer_name)
                if not DockerCmd(cmd):
                   print('One or more containers exists but are not parameterized.')
                   print('Please restart this lab with the "-r" option.')
                   DoStop(start_config, labtainer_config, lab_path, False)
                   logger.ERROR('One or more containers exists but not parameterized.')
                   sys.exit(1)
    
       	    # If the container is just created, then use the previous user's e-mail
            # then parameterize the container
            elif quiet_start and clone_need_seeds:
                ParamForStudent(start_config.lab_master_seed, mycontainer_name, container_user, container_password, 
                                labname, student_email, lab_path, name, image_info)
            
            elif clone_need_seeds:
                ParamForStudent(start_config.lab_master_seed, mycontainer_name, container_user, 
                                                 container_password, labname, student_email, lab_path, name, image_info)
    
        results.append(retval)

def GetUserEmail(quiet_start):
    user_email = None
    while user_email is None:
        done = True
        # Prompt user for e-mail address
        eprompt = 'Please enter your e-mail address: '
        prev_email = getLastEmail()
        if prev_email is not None:
            eprompt = eprompt+" [%s]" % prev_email

	    #checks if quiet_start is true
        if quiet_start and prev_email is not None:
            user_email = prev_email
        else:
            user_input = raw_input(eprompt)
            if not all(c in string.printable for c in user_input):
                print('Bad characters detected.  Please re-enter email')
            else:
                user_email = user_input 
        if user_email is not None:
            #user_email = raw_input(eprompt)
            if len(user_email.strip()) == 0:
                if prev_email is None:
                    print('You have provided an empty email address, which may cause your results to not be graded.')
                    confirm = str(raw_input('Use the empty address? (y/n)')).lower().strip()
                    if confirm != 'y':
                        user_email = None
                else:
                    user_email = prev_email
            else:
                putLastEmail(user_email)
    return user_email

def CheckLabContainerApps(start_config, lab_path, apps2start):
    apps2search = ['firefox', 'wireshark']
    has_multi_container = False
    num_containers = len(start_config.containers.items())
    if num_containers > 1:
        has_multi_container = True

    apps2startfilepath = os.path.join(lab_path, '*/_bin', 'student_startup.sh')
    apps2start_list = glob.glob('%s' % apps2startfilepath)

    if apps2start_list != []:
        # Parse each student_startup.sh - get a list of apps to start
        # Currently only search for firefox or wireshark
        for eachfile in apps2start_list:
            with open(eachfile) as fh:
                for line in fh:
                    if line.startswith('#') or len(line) == 0:
                        continue
                    for apps in apps2search:
                        if apps in line:
                            if apps not in apps2start:
                                apps2start.append(apps)

    return has_multi_container

def ReloadStartConfig(lab_path, labtainer_config, start_config, student_email, logger, servers, clone_count):
    
    labname = os.path.basename(lab_path)
    my_start_config = os.path.join('./.tmp',labname, 'start.config')
    if not os.path.isfile(my_start_config):
        config_path       = os.path.join(lab_path,"config") 
        start_config_path = os.path.join(config_path,"start.config")
        param_path = os.path.join(config_path,"parameter.config")
        try:
            os.makedirs(os.path.dirname(my_start_config))
        except os.error:
            pass
        shutil.copyfile(start_config_path, my_start_config)
        lab_instance_seed = GetLabSeed(start_config.lab_master_seed, student_email)
        logger.DEBUG("lab_instance_seed for <%s> <%s> is %s" % (start_config.lab_master_seed, student_email, lab_instance_seed))
        pp = ParameterParser.ParameterParser(None, None, lab_instance_seed, logger, lab=labname)
        pp.ParseParameterConfig(param_path)
        pp.DoReplace()
    start_config = ParseStartConfig.ParseStartConfig(my_start_config, labname, labtainer_config, logger, skip_networks=False,
                         servers=servers, clone_count=clone_count)
    logger.DEBUG('did start.config reload from %s' % my_start_config)
    return start_config


def CheckEmailReloadStartConfig(start_config, quiet_start, lab_path, labtainer_config, logger, servers, clone_count):
    student_email = None
    for name, container in start_config.containers.items():
        # Obscure means of making sure we have an email and getting one if
        # a container has not yet been created.
        if not AllContainersCreated(container) and student_email is None:
            if student_email == None:
                student_email = GetUserEmail(quiet_start)
            else:
                student_email = GetUserEmail(True)
    if student_email == None:
        student_email = GetUserEmail(True)
    start_config = ReloadStartConfig(lab_path, labtainer_config, start_config, student_email, logger, servers, clone_count)
    return start_config, student_email

def pidExists(pid):
    """Check whether pid exists in the current process table.
    UNIX only.
    """
    if pid <= 0:
        return False
    try:
        os.kill(pid, 0)
    except OSError as err:
        if err.errno == errno.ESRCH:
            # ESRCH == No such process
            return False
        elif err.errno == errno.EPERM:
            # EPERM clearly means there's a process to deny access to
            return True
        else:
            # According to "man 2 kill" possible error values are
            # (EINVAL, EPERM, ESRCH)
            raise
    else:
        return True

def ContainerTerminals(lab_path, start_config, container, terminal_count, terminal_groups):
    num_terminal = int(container.terminals)
    clone_names = GetContainerCloneNames(container)
    for mycontainer_name in clone_names:
        logger.DEBUG("container: %s  Number of terminals: %d" % (mycontainer_name, num_terminal))
        CopyFilesToHost(lab_path, container.name, mycontainer_name, container.user)
        ''' HACK remove after a while....  catch case where framework updated to remove XTERM Instructions, but still using image
            that includes instructions, which then consumes a window '''
        if container.xterm is None:
            cmd = "docker exec %s bash -c 'ls -l $HOME/instructions.txt'" % (mycontainer_name)
            if DockerCmd(cmd, noloop=True):
                logger.DEBUG('Found instructions, force xterm')
                container.xterm = 'instructions'

        if container.xterm is not None:
                logger.DEBUG('container.xterm is <%s>' % container.xterm)
                parts = container.xterm.split()
                title = parts[0]
                command = None
                if title.lower() == 'instructions' and len(parts) == 1:
                    command = 'startup.sh'
                elif len(parts) == 2:
                    command = parts[1]
                else:
                    logger.ERROR("Bad XTERM entryin in start.config: %s" % container.xterm)
                    exit(1)
                if command is not None:
                    cmd =  'sh -c "cd /home/%s && .local/bin/%s"' % (container.user, command)
                    terminal_location, columns, lines = terminalCounter(terminal_count)
                    terminal_count += 1
                    # note hack to change --geometry to -geometry
                    spawn_command = "xterm %s -title %s -sb -rightbar -fa 'Monospace' -fs 11 -e docker exec -it %s %s  & 2>/tmp/xterm.out" % (terminal_location[1:], 
                         title, mycontainer_name, cmd)
                    logger.DEBUG("xterm spawn: %s" % spawn_command)
                    xterm_pid = subprocess.Popen(shlex.split(spawn_command), stdout=subprocess.PIPE,stderr=subprocess.PIPE, close_fds=True).pid
                    # race condition, gnome may beat xterm to the startup.sh script
                    if command == 'startup.sh':
                        done = False
                        while pidExists(xterm_pid) and not done:
                            cmd = 'docker exec -it %s ls -l /tmp/.mylockdir' % mycontainer_name
                            ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
                            output = ps.communicate()
                            if 'No such file or directory' not in output[0]:
                                done = True
                            else:
                                time.sleep(0.2)
                    
        # If the number of terminals is -1 or zero -- do not spawn
        if not (num_terminal == 0 or num_terminal == -1):
            for x in range(num_terminal):
                #sys.stderr.write("%d \n" % terminal_count)
                terminal_location, columns, lines = terminalCounter(terminal_count)
                #sys.stderr.write("%s \n" % terminal_location)
                #sys.stderr.write("%s \n" % mycontainer_name)
                cmd = 'bash -l' 
                #spawn_command = "gnome-terminal %s -x docker exec -it %s bash -l &" % (terminal_location, mycontainer_name)
                if container.terminal_group is not None:
                    if container.terminal_group not in terminal_groups:
                        terminal_count += 1
                        terminal_groups[container.terminal_group] = []
                    group_command = '"docker exec -it %s %s"' % (mycontainer_name, cmd)
                    terminal_groups[container.terminal_group].append(group_command)
                else:
                    terminal_count += 1
                    spawn_command = 'gnome-terminal %s -- docker exec -it --env COLUMNS=%d --env LINES=%d %s %s &' % (terminal_location,
                       columns, lines, mycontainer_name, cmd)
                    logger.DEBUG("gnome spawn: %s" % spawn_command)
                    #print spawn_command
                    os.system(spawn_command)
    return terminal_count

def SkipContainer(run_container, name, start_config, servers):
    container = start_config.containers[name]
    if run_container is not None and container.full_name != run_container:
        return True
    if servers is not None:
        if servers == 'server':
            if container.client == 'yes':
                return True
        elif servers == 'client':
            if container.client != 'yes':
                return True
    return False

def readFirst(lab_path, labname, fname, quiet_start, bail_option=False):
    #
    #  If a fname exists in the lab's config directory, less it before the student continues.
    #
    doc_dir = os.path.join(lab_path, 'docs')
    read_first = os.path.join(doc_dir, fname)
    pdf = '%s.pdf' % labname
    manual = os.path.join(doc_dir, pdf)

    if os.path.isfile(read_first):
        print '\n\n'
        command = 'cat %s' % read_first
        less = subprocess.Popen(command.split(), stdout=subprocess.PIPE)
        sed_cmd = "sed -e s+LAB_MANUAL+%s+ -e s+LAB_DOCS+%s+" %  (manual, doc_dir)
        sed = subprocess.Popen(sed_cmd.split(), stdin=less.stdout, stdout=subprocess.PIPE)
        output = sed.communicate()[0]
        print output
        if not quiet_start: 
            less.wait()
            if not bail_option:
                dumb = raw_input("Press <enter> to start the lab\n")
            else:
                dumb = raw_input("Continue? (y/n)")
                if dumb.lower() != 'y':
                    cmd = 'rm -fr .tmp/%s' % labname
                    os.system(cmd)
                    print('Exiting lab')
                    exit(0)

def DoStart(start_config, labtainer_config, lab_path, 
            quiet_start, run_container, servers, clone_count, auto_grade=False, debug_grade=False, container_images=None):
    labname = os.path.basename(lab_path)
    logger.DEBUG("DoStart Multiple Containers and/or multi-home networking")
    ''' make sure root can access Xserver '''
    SetXhost()

    apps2start = []
    has_multi_container = CheckLabContainerApps(start_config, lab_path, apps2start)
    logger.DEBUG("Apps to start is (%s)" % apps2start)

    hostSystem_script = os.path.join(lab_path, '*/_bin', 'hostSystemCheck.py')
    hostSystemCheckList = glob.glob('%s' % hostSystem_script)
    logger.DEBUG("List of hostSystemCheck.py (%s)" % hostSystemCheckList)
    # If more than one hostSystemCheck.py - pick first one
    if hostSystemCheckList != [] and os.path.isfile(hostSystemCheckList[0]):
        # Do Host System Check if necessary (if file exists)
        command = "%s" % hostSystemCheckList[0]
        result = subprocess.call(shlex.split(command), stderr=subprocess.PIPE)
        if result == FAILURE:
            logger.WARNING("Host System Check indicates error encountered")
            user_input=raw_input("Would you like to quit? (yes/no)\n")
            user_input=user_input.strip().lower()
            #print "user_input (%s)" % user_input
            if user_input == "yes":
                sys.exit(1)

    # Create SUBNETS
    CreateSubnets(start_config)
    student_email = None
    threads = []
    results = []
    if has_multi_container:
        container_warning_printed = False
    start_config, student_email = CheckEmailReloadStartConfig(start_config, quiet_start, lab_path, 
                                      labtainer_config, logger, servers, clone_count)
    for name, container in start_config.containers.items():
        if SkipContainer(run_container, name, start_config, servers):
            #print('gonna skip %s' % run_container)
            continue

        if has_multi_container and container_warning_printed == False:
            print "Starting the lab, this may take a moment..."
            container_warning_printed = True
        image_info = None
        if container_images is not None:
            image_info = container_images[name]
        t = threading.Thread(target=DoStartOne, args=(labname, name, container, start_config, labtainer_config, lab_path, 
              student_email, quiet_start, results, auto_grade, image_info))
        threads.append(t)
        t.setName(name)
        t.start()
    logger.DEBUG('started all')
    for t in threads:
        t.join()
        logger.DEBUG('joined %s' % t.getName())

    if False in results:
        DoStop(start_config, labtainer_config, lab_path, False, run_container, servers)
        logger.ERROR('DoStartOne has at least one failure!')
        sys.exit(1)


    readFirst(lab_path, labname, 'read_first.txt', quiet_start)
    
    # spawn terminal for each container based on num_terminal
    terminal_count = 0
    terminal_groups = {}
    for name, container in start_config.containers.items():
        # Do not spawn terminal if it is regression testing
        if SkipContainer(run_container, name, start_config, servers):
            print('herez %s' % name)
            continue
        terminal_count = ContainerTerminals(lab_path, start_config, container, terminal_count, terminal_groups)

    for tg in terminal_groups:
        tab_commands = ''
        tab = '--window'
        for command in terminal_groups[tg]:
            tab_commands = tab_commands+' %s -e %s' % (tab, command)
            tab = '--tab'
            #tab_commands = tab_commands+' --tab %s --' % command
        terminal_location, columns, lines = terminalCounter(terminal_count)
        terminal_count += 1
        spawn_command = 'gnome-terminal %s %s' % (terminal_location, tab_commands)
        FNULL = open(os.devnull, 'w')
        result = subprocess.Popen(shlex.split(spawn_command), close_fds=True, stdout=FNULL, stderr=subprocess.STDOUT)
        logger.DEBUG("gnome spawn: %s" % spawn_command)
        #os.system(spawn_command)
                
    if apps2start != [] and not auto_grade:
        print "Please wait for the apps (%s) to launch" % apps2start

    return 0

def terminalCounter(terminal_count):
    columns = 100
    lines = 25
    x_coordinate = columns + ( 50 * terminal_count )
    y_coordinate = 75 + ( 50 * terminal_count)
    terminal_location = "--geometry %dx%d+%d+%d" % (columns, lines, x_coordinate, y_coordinate)
    return terminal_location, columns, lines

def terminalWideCounter(terminal_count):
    x_coordinate = 100 + ( 50 * terminal_count )
    y_coordinate = 75 + ( 50 * terminal_count)
    terminal_location = "--geometry 160x35+%d+%d" % (x_coordinate, y_coordinate)
    return terminal_location

# Check existence of /home/$USER/$HOST_HOME_XFER directory - create if necessary
def CreateHostHomeXfer(host_xfer_dir):
    # remove trailing '/'
    host_xfer_dir = host_xfer_dir.rstrip('/')
    logger.DEBUG("host_home_xfer directory (%s)" % host_xfer_dir)
    if os.path.exists(host_xfer_dir):
        # exists but is not a directory
        if not os.path.isdir(host_xfer_dir):
            # remove file then create directory
            os.remove(host_xfer_dir)
            os.makedirs(host_xfer_dir)
        #else:
        #    logger.DEBUG("host_home_xfer directory (%s) exists" % host_xfer_dir)
    else:
        # does not exists, create directory
        os.makedirs(host_xfer_dir)

# CopyChownGradesFile
def CopyChownGradesFile(start_config, labtainer_config, name, container_name, container_user, ignore_stop_error):
    host_home_xfer = os.path.join(labtainer_config.host_home_xfer, start_config.labname)
    labname = start_config.labname

    username = getpass.getuser()

    # Copy <labname>.grades.txt file
    grade_filename = '/home/%s/%s.grades.txt' % (container_user, labname)
    command = "docker cp %s:%s /home/%s/%s" % (container_name, grade_filename, username, host_home_xfer)
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(shlex.split(command))
    logger.DEBUG("Result of subprocess.Popen exec cp %s.grades.txt file is %s" % (labname, result))
    if result == FAILURE:
        # try grabbing instructor.log
        command = "docker cp %s:/tmp/instructor.log /tmp/instructor.log" % (container_name)
        result = subprocess.call(shlex.split(command))
        logger.DEBUG("Result of subprocess.Popen exec cp instructor.log file is %s" % (result))


        clone_names = GetContainerCloneNames(start_config.containers[name])
        for clone_full in clone_names:
            StopMyContainer(clone_full, ignore_stop_error)
            if ignore_stop_error:
                logger.DEBUG("Container %s fail on executing cp %s.grades.txt file!\n" % (container_name, labname))
            else:
                logger.WARNING("Container %s fail on executing cp %s.grades.txt file!\n" % (container_name, labname))
        return


    # Copy <labname>.grades.json file
    gradejson_filename = '/home/%s/%s.grades.json' % (container_user, labname)
    command = "docker cp %s:%s /home/%s/%s" % (container_name, gradejson_filename, username, host_home_xfer)
    logger.DEBUG("Command to execute is (%s)" % command)
    result = subprocess.call(shlex.split(command))
    logger.DEBUG("Result of subprocess.Popen exec cp %s.grades.json file is %s" % (labname, result))
    if result == FAILURE:
        clone_names = GetContainerCloneNames(container)
        for clone_full in clone_names:
            StopMyContainer(clone_full, ignore_stop_error)
            if ignore_stop_error:
                logger.DEBUG("Container %s fail on executing cp %s.grades.json file!\n" % (container_name, labname))
            else:
                logger.WARNING("Container %s fail on executing cp %s.grades.json file!\n" % (container_name, labname))
        return

def StartLab(lab_path, force_build=False, is_redo=False, quiet_start=False,
             run_container=None, servers=None, clone_count=None, auto_grade=False, debug_grade=False):
    labname = os.path.basename(lab_path)
    mycwd = os.getcwd()
    myhomedir = os.environ['HOME']
    logger.DEBUG("current working directory for %s" % mycwd)
    logger.DEBUG("current user's home directory for %s" % myhomedir)
    logger.DEBUG("ParseStartConfig for %s" % labname)
    is_valid_lab(lab_path)

    lab_count = LabCount.addCount('./', labname, is_redo, logger)
    if lab_count == 1:
        readFirst(lab_path, labname, 'read_pre.txt', quiet_start, bail_option=True)
    labtainer_config, start_config = GetBothConfigs(lab_path, logger, servers, clone_count)
    host_home_xfer = os.path.join(labtainer_config.host_home_xfer, labname)

    build_student = 'bin/buildImage.sh'
    build_instructor = 'bin/buildInstructorImage.sh'
    LABS_DIR = os.path.abspath('../../labs')
    didfix = False
    ''' hackey assumption about running from labtainers-student or labtainers-instructor '''
    container_bin = './bin'
    if is_redo or force_build:
        my_start_config = os.path.join('./.tmp',labname, 'start.config')
        if os.path.isfile(my_start_config):
            logger.DEBUG('Cached start.config removed %s' % my_start_config)
            os.remove(my_start_config)
       
    container_images = {} 
    for name, container in start_config.containers.items():
        if SkipContainer(run_container, name, start_config, servers):
            #print('skipping name %s %s' % (name, start_config.containers[name]))
            continue
        mycontainer_name       = container.full_name
        mycontainer_image_name = container.image_name
        if is_redo:
            # If it is a redo then always remove any previous container
            # If it is not a redo, i.e., start.py then DO NOT remove existing container
            clone_names = GetContainerCloneNames(container)
            for clone_full in clone_names:
                cmd = 'docker rm %s' % clone_full
                ps = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
                output = ps.communicate()
                logger.DEBUG("Command was (%s)" % cmd)
                if len(output[1]) > 0:
                    logger.DEBUG("Error from command = '%s'" % str(output[1]))
        #image_exists, result, dumb = ImageExists(mycontainer_image_name, container.registry)
        image_info = imageInfo(mycontainer_image_name, container.registry, labtainer_config, quiet=quiet_start)
        container_images[name] = image_info
        if image_info is not None:
            logger.DEBUG('Image version %s  framework_version %s' % (image_info.version, framework_version))
            if image_info.version is not None and int(image_info.version) > framework_version:
                print('**** Labtainer update required *****')
                print('This lab requires that you update your labtainers installation.')
                print('Please type:  update-labtainer.sh')
                print('and then try starting the lab again.') 
                exit(0)
            if not image_info.local:
                dockerPull(container.registry, mycontainer_image_name)
        else:
            if os.path.isfile(build_student):
                cmd = '%s %s %s %s %s %s %s %s %s %s' % (build_student, labname, name, container.user, container.password, False, 
                                                  LABS_DIR, labtainer_config.apt_source, container.registry, framework_version)
            elif os.path.isfile(build_instructor):
                cmd = '%s %s %s %s %s %s %s %s %s %s' % (build_instructor, labname, name, container.user, container.password, False, 
                                                  LABS_DIR, labtainer_config.apt_source, container.registry, framework_version)
            else:
                logger.ERROR("no image rebuild script\n")
                exit(1)
                    
            if os.system(cmd) != 0:
                logger.ERROR("build of image failed\n")
                exit(1)

    # Check existence of /home/$USER/$HOST_HOME_XFER directory - create if necessary
    host_xfer_dir = '%s/%s' % (myhomedir, host_home_xfer)
    CreateHostHomeXfer(host_xfer_dir)

    DoStart(start_config, labtainer_config, lab_path, quiet_start, 
            run_container, servers=servers, clone_count=clone_count, auto_grade=auto_grade, 
            debug_grade=debug_grade, container_images=container_images)

def DateIsLater(df_utc_string, ts, local=False, debug=False):
    parts = df_utc_string.split('.')
    ''' use dateutil to parse for zone, which we get from svn '''
    x=parse(parts[0])
    if local:
        df_ts = time.mktime(x.timetuple())
    else:
        df_ts = calendar.timegm(x.timetuple())
    if debug:
        logger.DEBUG('df_utc time is %s' % df_utc_string)
        logger.DEBUG('df_utc ts is %s given ts is %s' % (df_ts, ts))
    if int(df_ts) > int(ts):
        return True
    else:
        return False

def EmptyTar(fname):
    size = os.path.getsize(fname)
    if size == 10240 or size == 110:
        return True
    else:
        return False

def FileModLater(ts, fname):
    retval = False
    df_utc_string = None
    # start with check of svn status
    if os.path.isfile(fname):
        cmd = 'git ls-files -s %s' % fname
        child = subprocess.Popen(shlex.split(cmd), shell=False, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        output = child.communicate()
        if len(output[0].strip()) > 0:
            has_svn = True
        else:        
            has_svn = False
    else:
        has_svn = False
    cmd = 'git status -s %s' % fname
    logger.DEBUG('cmd: %s' % cmd)
    child = subprocess.Popen(shlex.split(cmd), shell=False, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    while True:
        line = child.stdout.readline()
        line = line.strip()
        if line == '':
            break
        logger.DEBUG('line: <%s>' % line)
        ''' ignore empty tar archives '''
        if line.startswith('?'):
            if os.path.isfile(fname):
                has_svn = False
            else:
                has_svn = True
            f = line.strip().split()[1]
            if f.endswith('.tar'):
                if EmptyTar(f):
                    continue
                fdir = os.path.dirname(f)
                if os.path.isfile(os.path.join(fdir,'external-manifest')):
                    continue
            elif f.endswith('_tar') and os.path.isdir(f):
                continue
            elif os.path.isfile(f):
                df_time = os.path.getmtime(f)
                df_utc_string = str(datetime.datetime.utcfromtimestamp(df_time))
                retval = DateIsLater(df_utc_string, ts)
                if retval:
                    break
        else:
            has_svn = True
         
        #logger.DEBUG(line)
        if os.path.isdir(fname) or line.startswith('M') or line.startswith('>'):
            if '/home_tar/' in line or '/sys_tar/' in line:
                continue
            logger.DEBUG('svn status found something for fname %s, line %s' % (fname, line))
            if line.startswith('M'):
                file_path = line.split()[-1]
                df_time = os.path.getmtime(file_path)
                #parent = os.path.dirname(line.split()[1])
                #df_time = os.path.getmtime(parent)
            elif line.startswith('D'):
                file_path = line.split()[-1]
                if '/' in file_path:
                    file_dir = os.path.dirname(file_path)
                    df_time = os.path.getmtime(file_dir)
            else:
                file_path = '/'+line.split('/', 1)[-1].strip()
                #logger.DEBUG('not an "M", get dftime for %s' % file_path)
                if not os.path.exists(file_path):
                    continue
                df_time = os.path.getmtime(file_path)
            df_utc_string = str(datetime.datetime.utcfromtimestamp(df_time))
            retval = DateIsLater(df_utc_string, ts, debug=False)
            if retval:
                break
    if df_utc_string is None:
        # try svn info.  stderr implies not in svn
        if not has_svn: 
            #logger.DEBUG('not in svn?')
            # assume not in svn
            #logger.DEBUG("not in svn? %s" % fname)
            if fname.endswith('.tar'):
                if EmptyTar(fname):
                    # hacky special case for empty tar files.  ug.
                    return False
                fdir = os.path.dirname(fname)
                # why not consider tars built from external manifest???
                #if os.path.isfile(os.path.join(fdir,'external-manifest')):
                #    return False
            if os.path.isfile(fname):
                df_time = os.path.getmtime(fname)
            else:
                check_file = newest_file_in_tree(fname)
                #logger.DEBUG('latest found is %s' % check_file)
                if EmptyTar(check_file):
                    # hacky special case for empty tar files.  ug.
                    return False
                df_time = os.path.getmtime(check_file)
            df_utc_string = str(datetime.datetime.utcfromtimestamp(df_time))
            retval = DateIsLater(df_utc_string, ts)
        else:
            # in svn, look for changed date
            cmd = 'git log -1 --format="%%ad" %s' % fname
            logger.DEBUG('in svn, look for changed date %s' % cmd)
            child = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
            output = child.communicate()
            if len(output[0].strip()) > 0:
                df_utc_string = output[0].strip()
                logger.DEBUG('git log for %s returned %s' % (cmd, df_utc_string))
                svn_is_later = DateIsLater(df_utc_string, ts, local=True, debug=False)
                df_time = os.path.getmtime(fname)
                file_utc_string = str(datetime.datetime.utcfromtimestamp(df_time))
                logger.DEBUG('file time %s' % file_utc_string)
                file_is_later = DateIsLater(file_utc_string, ts, local=False, debug=False)
                retval = svn_is_later and file_is_later

            if df_utc_string is None:
                # must be an add
                logger.DEBUG('%s must be an add' % fname)
                if os.path.isfile(fname):
                    df_time = os.path.getmtime(fname)
                else:
                    check_file = newest_file_in_tree(fname)
                    logger.DEBUG('latest found is %s' % check_file)
                    df_time = os.path.getmtime(check_file)
                df_utc_string = str(datetime.datetime.utcfromtimestamp(df_time))
                retval = DateIsLater(df_utc_string, ts, debug=False)

    ''' is the given file later than the timestamp (which is in UTC)? '''
    #logger.DEBUG('df ts %s' % df_time)
    return retval

def BaseImageTime(dockerfile, registry):
    image_name = None
    retval = 0
    with open(dockerfile) as fh:
        for line in fh:
            if line.strip().startswith('FROM'):
                parts = line.strip().split()
                image_name = parts[1]
                image_name = image_name.replace("$registry", registry)
                break
    if image_name is None:
        logger.ERROR('no base image found in %s' % dockerfile)
        exit(1)
    image_exists, result, dumb = ImageExists(image_name, None)
    if image_exists:
        parts = result.strip().split('.')
        #time_string = parts[0]
        #logger.DEBUG('base image time string %s' % time_string)
        #retval = time.mktime(time.strptime(time_string, "%Y-%m-%dT%H:%M:%S"))
        x=parse(parts[0])
        retval = calendar.timegm(x.timetuple())
        logger.DEBUG('base image time string %s returning %s' % (parts[0], retval))
    else:
        logger.DEBUG('base image %s not found, assume not updated' % image_name)
    return retval, image_name
 
def newest_file_in_tree(rootfolder):
    if len(os.listdir(rootfolder)) > 0:
        try:
            return max(
                (os.path.join(dirname, filename)
                for dirname, dirnames, filenames in os.walk(rootfolder)
                for filename in filenames),
                key=lambda fn: os.stat(fn).st_mtime)
        except ValueError:
            return rootfolder
    else:
        return rootfolder

def GetImageUser(image_name, container_registry):
    
    user = None
    password = None
    cmd = 'docker history --no-trunc %s' % image_name
    child = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    output = child.communicate()
    if len(output[1]) > 0:
        cmd = 'docker history --no-trunc %s/%s' % (container_registry, image_name)
        child = subprocess.Popen(shlex.split(cmd), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        output = child.communicate()
    if len(output[0]) > 0:
        for line in output[0].splitlines(True):
            parts = line.split()
            for p in parts:
                if p.startswith('user_name='):
                    user = p.split('=')[1]
                elif p.startswith('password='):
                    password = p.split('=')[1]
            if user is not None:
                return user, password 
    return user, password
                
def CheckBuild(lab_path, image_name, image_info, container_name, name, is_redo, container_bin,
                 start_config, container_registry, container_user):
    '''
    Determine if a container image needs to be rebuilt, return true if so.
    '''
    
    container_dir = os.path.join(lab_path, name)
    labname = os.path.basename(lab_path)
    should_be_exec = ['rc.local', 'fixlocal.sh']
    retval = False

    #image_exists, result, dumb = ImageExists(image_name, container_registry)
    if image_info is not None and not is_redo:
        logger.DEBUG('Container %s image %s exists, not a redo, just return (no need to check build)' % (container_name, image_name))
        return False
    elif image_info is None:
        return True 

    x=parse(image_info.creation)
    ts = calendar.timegm(x.timetuple())
    logger.DEBUG('image ts %s  %s' % (ts, image_info.creation))
   
    ''' look at dockerfiles '''
    df_name = 'Dockerfile.%s' % container_name
    df = os.path.join(lab_path, 'dockerfiles', df_name)
    if not os.path.isfile(df):
         df = df.replace('instructor', 'student')

    ''' get ts of base image '''
    ts_base, bname = BaseImageTime(df, container_registry)
    if ts_base > ts:
        logger.WARNING('Base image %s changed, will build %s' % (bname, name))
        retval = True
    elif FileModLater(ts, df):
        logger.WARNING('dockerfile changed, will build %s' % name)
        retval = True
    else:
        ''' look for new/deleted files in the container '''
        logger.DEBUG('container dir %s' % container_dir)
        if FileModLater(ts, container_dir):
           logger.WARNING('new/deleted %s is later, will build %s' % (container_dir, name))
           retval = True
        else:
            ''' look at all files/directories in container '''
            flist = os.listdir(container_dir)
            for f in flist:
                check_file = None
                if f == 'sys_tar':
                    check_file = os.path.join(container_dir, f, 'sys.tar')
                elif f == 'home_tar':
                    check_file = os.path.join(container_dir, f, 'home.tar')
                elif os.path.isdir(os.path.join(container_dir,f)):
                    check_file = newest_file_in_tree(os.path.join(container_dir, f))
                else:
                    check_file = os.path.join(container_dir, f)
                logger.DEBUG('check file %s' % check_file)
                if FileModLater(ts, check_file):
                    logger.WARNING('files in container %s is later, will build %s' % (check_file, name))
                    retval = True
                    break

    if not retval:
        param_file = os.path.join(lab_path, 'config', 'parameter.config')
        if os.path.isfile(param_file):
            if FileModLater(ts, param_file):
              logger.DEBUG('%s is later, see if container is named' % param_file)
              with open(param_file) as param_fh:
                for line in param_fh:
                    if line.startswith('#') or ' : ' not in line:
                        continue
                    parts = line.split(' : ')
                    filenames = parts[2].split(';')
                    for fname in filenames: 
                        fname = f.strip()
                        # look for container, or lack of any container qualifier in file name
                        if fname != 'start.config':
                            if fname.startswith(container_name+':') or len(parts)<3 or ':' not in fname:
                                logger.WARNING('%s is later and %s mentioned in it, will build' % (param_file, container_name))
                                retval = True
                                break
                    if retval:
                        break
    
    #if not retval and container_bin is not None:
    #    all_bin_files = os.listdir(container_bin)
    #    for f in all_bin_files:
    #        if f.endswith('.swp'):
    #            continue
    #        f_path = os.path.join(container_bin, f)
    #        if FileModLater(ts, f_path):
    #           logger.WARNING('container_bin %s is later, will build %s' % (f_path, name))
    #           retval = True
    #           break

    if not retval and image_info.local:
        user, password = GetImageUser(image_name, container_registry)
        if user != container_user:
            logger.WARNING('user changed from %s to %s, will build %s' % (user, container_user, name))
            retval = True

    logger.DEBUG('returning retval of %s' % str(retval))    
    return retval

def dumb():
    pass
    '''
    '''
def RedoLab(lab_path, force_build=False, is_redo=False, quiet_start=False,
             run_container=None, servers=None, clone_count=None, auto_grade=False, debug_grade=False):
    mycwd = os.getcwd()
    myhomedir = os.environ['HOME']
    # Pass 'True' to ignore_stop_error (i.e., ignore certain error encountered during StopLab
    #                                         since it might not even be an error)
    StopLab(lab_path, True)
    is_redo = True
    StartLab(lab_path, force_build, is_redo=is_redo, quiet_start=quiet_start,
             run_container=run_container, servers=servers, clone_count=clone_count, auto_grade=auto_grade, debug_grade=debug_grade)

def CheckShutdown(lab_path, name, container_name, container_user, ignore_stop_error):
    ''' NOT USED at the moment '''
    done = False
    count = 0
    while not done:
        command='docker cp %s:/tmp/.shutdown_done /tmp/' % (container_name)
        logger.DEBUG(command)
        child = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        error = child.stderr.read().strip()
        if len(error) > 0:
           logger.DEBUG("response from docker cp %s" % error)
           time.sleep(1)
        else:
           logger.DEBUG("must have found the shutdown_done file")
           done = True
        count += 1
        if count > 5:
           done = True

def PreStop(container_name, ts):
    logger.DEBUG("About to call prestop")
    cmd_path = '$HOME/.local/bin/prestop'
    cmd = "docker exec %s bash -c 'ls -l %s'" % (container_name, cmd_path)

    if DockerCmd(cmd, noloop=True):
        cmd = "docker exec %s bash -c '%s >$HOME/.local/result/prestop.stdout.%s'" % (container_name, cmd_path, ts)
        DockerCmd(cmd, noloop=True)

def GatherOtherArtifacts(lab_path, name, container_name, container_user, container_password, ignore_stop_error):
    '''
    Parse the results.config file looking for files named by absolute paths,
    and copy those into the .local/result directory, maintaining the original
    directory structure, e.g., .local/result/var/log/foo.log
    '''
    config_path       = os.path.join(lab_path,"instr_config") 
    results_config_path = os.path.join(config_path,"results.config")
    did_file = []
    CopyAbsToResult(container_name, '/root/.bash_history', container_user, ignore_stop_error) 
    did_file.append('/root/.bash_history')
    with open (results_config_path) as fh:
        for line in fh:
            ''' container:filename is between "=" and first " : " '''
            line = line.strip()
            if line.startswith('#') or len(line) == 0:
                continue
            if '=' not in line:
                logger.WARNING('no = in line %s' % line)
                continue
            after_equals = line.split('=', 1)[1].strip()
            # note assumes field delimeters are space-:-space, vice container:file 
            fname = after_equals.split(' : ')[0].strip()
            is_mine = False
            if ':' in fname:
                '''
                [container_name:]<prog>.[stdin | stdout] | [container_name:]file_path[:time_program]
 
                '''
                f_container = None
                parts = fname.split(':')
                if len(parts) == 2:
                    if parts[0].startswith('/'):
                        filename =  parts[0]
                    else:
                        f_container = parts[0]
                        filename = parts[1]
                elif len(parts) == 3:
                    f_container = parts[0]
                    filename = parts[1]
                if f_container is not None and f_container.strip() == name:
                    is_mine = True 
                filename = filename.strip()
            else: 
                is_mine = True
                filename = fname
            if is_mine:
                if filename.startswith('/') and filename not in did_file:
                    ''' copy from abs path to ~/.local/result ''' 
                    logger.DEBUG('file on this container to copy <%s>' % filename )
                    CopyAbsToResult(container_name, filename, container_user, ignore_stop_error) 
                    did_file.append(filename)
                        
def CopyAbsToResult(container_name, fname, container_user, ignore_stop_error):
    ''' copy from abs path to ~/.local/result '''

    command='docker exec %s mkdir -p /home/%s/.local/result' % (container_name, container_user)
    command='docker exec %s sudo  cp --parents %s /home/%s/.local/result' % (container_name, fname, container_user)
    logger.DEBUG(command)
    child = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    error = child.stderr.read().strip()
    if len(error) > 0:
        if ignore_stop_error:
            logger.DEBUG('error from docker: %s' % error)
            logger.DEBUG('command was %s' % command)
        else:
            logger.DEBUG('error from docker: %s' % error)
            logger.DEBUG('command was %s' % command)
    #command='docker exec %s echo "%s\n" | sudo -S chmod a+r -R /home/%s/.local/result' % (container_name, container_password, container_user)
    command='docker exec %s sudo chmod a+r -R /home/%s/.local/result' % (container_name, container_user)
    child = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    error = child.stderr.read().strip()
    if len(error) > 0:
        if ignore_stop_error:
            logger.DEBUG('chmod ERROR: %s' % error)
            logger.DEBUG('command was %s' % command)
        else:
            logger.ERROR('chmod ERROR: %s' % error)
            logger.ERROR('command was %s' % command)


def CreateCopyChownZip(start_config, labtainer_config, name, container_name, container_image, container_user, 
                       container_password, ignore_stop_error, keep_running):
    '''
    Zip up the student home directory and copy it to the Linux host home directory
    '''
    logger.DEBUG('in CreateCopyChownZip')
    host_home_xfer  = os.path.join(labtainer_config.host_home_xfer, start_config.labname)

    # Run 'Student.py' - This will create zip file of the result
    logger.DEBUG("About to call Student.py")
    ''' Copy the Student.py on each stop to handle cases where the parameter list changes.'''
    cmd = 'docker cp lab_bin/Student.py  %s:/home/%s/.local/bin/' % (container_name, container_user)
    if not DockerCmd(cmd):
        logger.ERROR('failed to copy Student.py')
    cmd_path = '/home/%s/.local/bin/Student.py' % (container_user)
    #command=['docker', 'exec', '-i',  container_name, 'echo "%s\n" |' % container_password, '/usr/bin/sudo', cmd_path, container_user, container_image]
    command=['docker', 'exec', '-i',  container_name, '/usr/bin/sudo', cmd_path, container_user, container_image, str(keep_running)]
    logger.DEBUG('cmd: %s' % str(command))
    child = subprocess.Popen(command, shell=False, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    output = child.communicate()
    if keep_running and len(output[0].strip()) > 0:
        print('\n<<<<< You may need to stop: %s in order to obtain a complete assessment. >>>>>\n' % output[0].strip())
    if len(output[1].strip()) > 0:
        if ignore_stop_error:
            logger.DEBUG("Container %s fail on executing Student.py %s \n" % (container_name, output[1]))
        else:
            logger.ERROR("Container %s fail on executing Student.py %s \n" % (container_name, output[1]))
        return None, None
    logger.DEBUG("results from Student.py: %s" % output[0])
    
    #out_string = output[0].strip()
    #if len(out_string) > 0:
    #    logger.DEBUG('output of Student.py is %s' % out_string)
    username = getpass.getuser()

    tmp_dir=os.path.join('/tmp/labtainers', container_name)
    shutil.rmtree(tmp_dir, ignore_errors=True)
    try:
        os.makedirs(tmp_dir)
    except os.error:
        logger.ERROR("did not expect to find dir %s" % tmp_dir)
    source_dir = os.path.join('/home', container_user, '.local', 'zip')
    cont_source = '%s:%s' % (container_name, source_dir)
    logger.DEBUG('will copy from %s ' % source_dir)
    command = ['docker', 'cp', cont_source, tmp_dir]
    # The zip filename created by Student.py has the format of e-mail.labname.zip
    logger.DEBUG("Command to execute is (%s)" % command)
    child = subprocess.Popen(command, shell=False, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    error_string = child.stderr.read().strip()
    if len(error_string) > 0:
        if ignore_stop_error:
            logger.DEBUG("Container %s fail on executing cp zip file: %s\n" % (container_name, error_string))
            logger.DEBUG("Command was (%s)" % command)
        else:
            logger.ERROR("Container %s fail on executing cp zip file: %s\n" % (container_name, error_string))
            logger.ERROR("Command was (%s)" % command)
        clone_names = GetContainerCloneNames(start_config.containers[name])
        for clone_full in clone_names:
            StopMyContainer(clone_full, ignore_stop_error)
        return None, None
    
    local_tmp_zip = os.path.join(tmp_dir, 'zip')
    try:
        orig_zipfilenameext = os.listdir(local_tmp_zip)[0]
    except:
        if ignore_stop_error:
            logger.DEBUG('no files at %s\n' % local_tmp_zip)
        else:
            logger.ERROR('no files at %s\n' % local_tmp_zip)
        return None, None
    orig_zipfilename, orig_zipext = os.path.splitext(orig_zipfilenameext)
    baseZipFilename = os.path.basename(orig_zipfilename)
    #NOTE: Use the '=' to separate e-mail+labname from the container_name
    DestZipFilename = '%s=%s.zip' % (baseZipFilename, container_name)
    DestZipPath = os.path.join('/home', username, host_home_xfer, DestZipFilename)
    shutil.copyfile(os.path.join(local_tmp_zip, orig_zipfilenameext), DestZipPath)

    currentContainerZipFilename = "/home/%s/%s/%s" % (username, host_home_xfer, DestZipFilename)
    return baseZipFilename, currentContainerZipFilename
   
# Stop my_container_name container
def StopMyContainer(container_name, ignore_stop_error):
    command = "docker stop -t 1 %s" % container_name
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        if ignore_stop_error:
            logger.DEBUG('Fail to stop container, error returned %s' % output[1])
        else:
            logger.ERROR('Fail to stop container, error returned %s' % output[1])
    #if len(output[0].strip()) > 0:
    #    logger.DEBUG('StopMyContainer stdout %s' % output[0])
    #result = subprocess.call(command, shell=True)

# Get a list of running lab
def GetListRunningLab():
    lablist = []
    # Note: doing "docker ps" not "docker ps -a" to get just the running container
    command = "docker ps"
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        logger.ERROR('Fail to get a list of running containers, error returned %s' % output[1])
        sys.exit(1)
    if len(output[0]) > 0:
        docker_ps_output = output[0].split('\n')
        for each_line in docker_ps_output:
            # Skip empty line or the "CONTAINER ID" line - the header line returned by "docker ps"
            current_line = each_line.strip()
            if not current_line or current_line.startswith("CONTAINER"):
                continue
            # Assume the container name is the last token on the line
            container_info = current_line.split()
            container_name = container_info[-1]
            # Assume the labname is the first token if split by '.'
            labname = container_name.split('.')[0]
            if labname not in lablist:
                lablist.append(labname)
    return lablist

# Given a network name, if it is valid, get a list of labname for the container(s) that is(are)
# using that network. Note: the network name is passed in as an argument
def GetListLabContainerOnNetwork(network_name):
    containerlabnamelist = []
    command = "docker network inspect %s" % network_name
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        logger.ERROR('Fail to inspect the network %s, error returned %s' % (network_name, output[1]))
        sys.exit(1)
    if len(output[0]) > 0:
        network_result = json.loads(output[0])
        if len(network_result) != 0:
            result = network_result[0]
            containers = result["Containers"]
            for key in containers:
                container_name = containers[key]["Name"]
                # Assume the labname is the first token if split by '.'
                labname = container_name.split('.')[0]
                if labname not in containerlabnamelist:
                    containerlabnamelist.append(labname)
    return containerlabnamelist

# Given an IP address (gateway IP address) - find a network name that has that IP address as its gateway
# Note: the IP address is passed in as an argument
def FindNetworkGivenGatewayIP(gateway_address):
    found_match_network = False
    found_match_network_name = ""
    logger.DEBUG("FindNetworkGivenGatewayIP %s" % gateway_address)
    networklist = []
    # First get a list of network name of driver=bridge
    command = "docker network ls --filter driver=bridge"
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        logger.ERROR('Fail to get a list of network (driver=bridge), error returned %s' % output[1])
        sys.exit(1)
    if len(output[0]) > 0:
        network_list = output[0].split('\n')
        for each_line in network_list:
            # Skip empty line or the "NETWORK ID" line - the header line returned by "docker network"
            current_line = each_line.strip()
            if not current_line or current_line.startswith("NETWORK"):
                continue
            # Assume the network name is the second token on the line
            container_info = current_line.split()
            network_name = container_info[1]
            # Do not need to check network name "bridge"
            if network_name != "bridge" and network_name not in networklist:
                networklist.append(network_name)
    # Loop through each network (driver=bridge) to find if any uses IP address as gateway
    for network_name in networklist:
        command = "docker network inspect %s" % network_name
        logger.DEBUG("Command to execute is (%s)" % command)
        ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        output = ps.communicate()
        if len(output[1].strip()) > 0:
            logger.ERROR('Fail to inspect the network %s, error returned %s' % (network_name, output[1]))
            sys.exit(1)
        if len(output[0]) > 0:
            network_result = json.loads(output[0])
            if len(network_result) != 0:
                result = network_result[0]
                ipam_config = result["IPAM"]["Config"][0]
                for key in ipam_config:
                    if key == "Gateway":
                        ipam_config_gateway_ip = ipam_config[key]
                        if gateway_address == ipam_config_gateway_ip:
                            found_match_network = True
                            found_match_network_name = network_name
                            break
    return found_match_network, found_match_network_name

# Given a subnet (network subnet) - find a network name that has that same subnet
# Note: the subnet is passed in as an argument
def FindNetworkGivenSubnet(subnet):
    found_match_network = False
    found_match_network_name = ""
    logger.DEBUG("FindNetworkGivenSubnet %s" % subnet)
    networklist = []
    # First get a list of network name of driver=bridge
    command = "docker network ls --filter driver=bridge"
    logger.DEBUG("Command to execute is (%s)" % command)
    ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1].strip()) > 0:
        logger.ERROR('Fail to get a list of network (driver=bridge), error returned %s' % output[1])
        sys.exit(1)
    if len(output[0]) > 0:
        network_list = output[0].split('\n')
        for each_line in network_list:
            # Skip empty line or the "NETWORK ID" line - the header line returned by "docker network"
            current_line = each_line.strip()
            if not current_line or current_line.startswith("NETWORK"):
                continue
            # Assume the network name is the second token on the line
            container_info = current_line.split()
            network_name = container_info[1]
            # Do not need to check network name "bridge"
            if network_name != "bridge" and network_name not in networklist:
                networklist.append(network_name)
    # Loop through each network (driver=bridge) to find if any that has the same subnet
    for network_name in networklist:
        command = "docker network inspect %s" % network_name
        logger.DEBUG("Command to execute is (%s)" % command)
        ps = subprocess.Popen(shlex.split(command), stdout=subprocess.PIPE,stderr=subprocess.PIPE)
        output = ps.communicate()
        if len(output[1].strip()) > 0:
            logger.ERROR('Fail to inspect the network %s, error returned %s' % (network_name, output[1]))
            sys.exit(1)
        if len(output[0]) > 0:
            network_result = json.loads(output[0])
            if len(network_result) != 0:
                result = network_result[0]
                ipam_config = result["IPAM"]["Config"][0]
                for key in ipam_config:
                    if key == "Subnet":
                        ipam_config_subnet = ipam_config[key]
                        if subnet == ipam_config_subnet:
                            found_match_network = True
                            found_match_network_name = network_name
                            break
    return found_match_network, found_match_network_name

def AllContainersRunning(container):
    clone_names = GetContainerCloneNames(container)
    for clone_full in clone_names:
        if not IsContainerRunning(clone_full):
            return False
    return True

def AnyContainersRunning(container):
    clone_names = GetContainerCloneNames(container)
    for clone_full in clone_names:
        if not IsContainerRunning(clone_full):
            return False
    return True

def IsContainerRunning(mycontainer_name):
    cmd = 'docker ps -f name=%s' % mycontainer_name
    try:
        s = subprocess.check_output(shlex.split(cmd))
    except:
        return False
    if mycontainer_name in s:
        return True
    else:
        return False 

def ShouldBeRunning(start_config, container):
    if start_config.multi_user is not None and start_config.multi_user != 'clones':
        if start_config.multi_user == 'server' and container.client == 'yes':
            return False
        if start_config.multi_user == 'client' and container.client != 'yes':
            return False
    return True
       
   
def DoStopOne(start_config, labtainer_config, lab_path, name, container, ZipFileList, ignore_stop_error, results, keep_running):
        labname = os.path.basename(lab_path) 
        #dumlog = os.path.join('/tmp', name+'.log')
        #sys.stdout = open(dumlog, 'w')
        #sys.stderr = sys.stdout
        retval = True
        mycontainer_name  = container.full_name
        container_user    = container.user
        container_password    = container.password
        mycontainer_image = container.image_name
        haveContainer     = AllContainersCreated(container)
        logger.DEBUG("AllContainersCreated for %s result (%s)" % (container.name, haveContainer))

        # IsContainerCreated returned FAILURE if container does not exists
        # error: can't stop non-existent container
        if not haveContainer:
            if ShouldBeRunning(start_config, container) and not ignore_stop_error:
                logger.ERROR("Container %s does not exist!\n" % mycontainer_name)
                retval = False

        else:
            clone_names = GetContainerCloneNames(container)
            for mycontainer_name in clone_names:
                if not IsContainerRunning(mycontainer_name):
                    if ShouldBeRunning(start_config, container):
                        if ignore_stop_error:
                            logger.DEBUG("container %s not running\n" % (mycontainer_name))
                        else:
                            logger.ERROR("container %s not running\n" % (mycontainer_name))
                            retval = False
                    continue
                GatherOtherArtifacts(lab_path, name, mycontainer_name, container_user, container_password, ignore_stop_error)
                # Before stopping a container, run 'Student.py'
                # This will create zip file of the result
    
                baseZipFilename, currentContainerZipFilename = CreateCopyChownZip(start_config, labtainer_config, name, 
                             mycontainer_name, mycontainer_image, container_user, container_password, ignore_stop_error, keep_running)
                if baseZipFilename is not None:
                    ZipFileList.append(currentContainerZipFilename)
                logger.DEBUG("baseZipFilename is (%s)" % baseZipFilename)

                #command = 'docker exec %s echo "%s\n" | sudo -S rmdir /tmp/.mylockdir 2>/dev/null' % (mycontainer_name, container_password)
                command = 'docker exec %s sudo rmdir /tmp/.mylockdir 2>/dev/null' % (mycontainer_name)
                os.system(command)
                if not keep_running:
                    did_this = []
                    for mysubnet_name, mysubnet_ip in container.container_nets.items():
                        subnet_name = mysubnet_name
                        if ':' in mysubnet_name:
                            subnet_name = mysubnet_name.split(':')[0] 
                        if subnet_name not in did_this:
                            disconnectNetworkResult = DisconnectNetworkFromContainer(mycontainer_name, subnet_name)
                            did_this.append(subnet_name)

                # Stop the container
            
                if not keep_running:
                    StopMyContainer(mycontainer_name, ignore_stop_error)

        results.append(retval)

def SynchStop(start_config, run_container=None):
    threads = []
    now = datetime.datetime.now()
    ts = now.strftime('%Y%m%d%H%M%S')
    for name, container in start_config.containers.items():
        if run_container is not None and container.full_name != run_container:
            #print('not for me %s ' % run_container)
            continue
        clone_names = GetContainerCloneNames(container)
        for mycontainer_name in clone_names:
            t = threading.Thread(target=PreStop, args=[mycontainer_name, ts])
            threads.append(t)
            t.setName(name)
            t.start()
      
        logger.DEBUG('prestop started on all')
        for t in threads:
            t.join()
            logger.DEBUG('joined %s' % t.getName())

def DoStop(start_config, labtainer_config, lab_path, ignore_stop_error, run_container=None, servers=None, clone_count=None, keep_running=False):
    mycwd = os.getcwd()
    retval = True
    labname = os.path.basename(lab_path)
    host_home_xfer  = os.path.join(labtainer_config.host_home_xfer, labname)
    logger.DEBUG("DoStop Multiple Containers and/or multi-home networking, keep_running is %r" % keep_running)
    SynchStop(start_config, run_container)
    username = getpass.getuser()

    baseZipFilename = ""
    ZipFileList = []
    threads = []
    results = []
    for name, container in start_config.containers.items():
        if run_container is not None and container.full_name != run_container:
            #print('not for me %s ' % run_container)
            continue
        mycontainer_name = '%s.%s.student' % (labname, container.name)

        t = threading.Thread(target=DoStopOne, args=(start_config, labtainer_config, lab_path, 
              name, container, ZipFileList, ignore_stop_error, results, keep_running))
        threads.append(t)
        t.setName(name)
        t.start()
      
    logger.DEBUG('stopped all')
    for t in threads:
        t.join()
        logger.DEBUG('joined %s' % t.getName())

    if not keep_running:
        RemoveSubnets(start_config.subnets, ignore_stop_error)
    if not ignore_stop_error:
        if False in results:
            logger.ERROR('DoStopOne has at least one failure!')
            sys.exit(1)

    if len(ZipFileList) == 0:
        if ignore_stop_error:
            logger.DEBUG('No zip files found')
        else:
            logger.ERROR('No zip files found')
        return None
    base_filename = os.path.basename(ZipFileList[0])
    baseZipFilename = base_filename.split('=')[0]

    xfer_dir = "/home/%s/%s" % (username, host_home_xfer)

    # Create docs.zip in xfer_dir if COLLECT_DOCS is "yes"
    if start_config.collect_docs.lower() == "yes":
        docs_zip_file = "%s/docs.zip" % xfer_dir
        logger.DEBUG("Zipping docs directory to %s" % docs_zip_file)

        docs_path = '%s/docs' % lab_path
        if os.path.isdir(docs_path):
            docs_zip_filelist = glob.glob('%s/*' % docs_path)
            logger.DEBUG(docs_zip_filelist)

            # docs.zip file
            docs_zipoutput = zipfile.ZipFile(docs_zip_file, "w")
            # Go to the docs_path
            os.chdir(docs_path)
            for docs_fname in docs_zip_filelist:
                docs_basefname = os.path.basename(docs_fname)
                docs_zipoutput.write(docs_basefname, compress_type=zipfile.ZIP_DEFLATED)
                # Note: DO NOT remove after the file is zipped
            docs_zipoutput.close()

            # Add docs.zip into the ZipFileList
            ZipFileList.append(docs_zip_file)
        else:
            logger.DEBUG('no docs at %s' % docs_path)

    # Combine all the zip files
    logger.DEBUG("ZipFileList is ")
    logger.DEBUG(ZipFileList)
    logger.DEBUG("baseZipFilename is (%s)" % baseZipFilename)
    combinedZipFilename = "%s/%s.zip" % (xfer_dir, baseZipFilename)
    logger.DEBUG("The combined zip filename is %s" % combinedZipFilename)
    zipoutput = zipfile.ZipFile(combinedZipFilename, "w")
    # Go to the xfer_dir
    os.chdir(xfer_dir)
    for fname in ZipFileList:
        basefname = os.path.basename(fname)
        zipoutput.write(basefname, compress_type=zipfile.ZIP_DEFLATED)
        # Remove after the file is zipped
        os.remove(basefname)

    # Add count.json and labtainer.log (if they exist) to the zip file
    count_path = LabCount.getPath('./', labname)
    #print "count_path is %s" % count_path
    if os.path.isfile(count_path):
        parent = os.path.dirname(count_path)
        os.chdir(mycwd)
        os.chdir(parent)
        fname = os.path.join('./', os.path.basename(count_path))
        zipoutput.write(fname, compress_type=zipfile.ZIP_DEFLATED)
    os.chdir(mycwd)
    my_labtainer_log = os.path.join('./', 'labtainer.log')
    if os.path.exists(my_labtainer_log):
        zipoutput.write(my_labtainer_log, compress_type=zipfile.ZIP_DEFLATED)

    zipoutput.close()
    post_zip = os.path.join(lab_path, 'bin', 'postzip')
    if os.path.isfile(post_zip):
         cmd = "%s %s" % (post_zip, combinedZipFilename)
         os.system(cmd)

    os.chdir(mycwd)
    return retval

# ignore_stop_error - set to 'False' : do not ignore error
# ignore_stop_error - set to 'True' : ignore certain error encountered since it might not even be an error
#                                     such as error encountered when trying to stop non-existent container
def StopLab(lab_path, ignore_stop_error, run_container=None, servers=None, clone_count=None, keep_running=False):
    labname = os.path.basename(lab_path)
    myhomedir = os.environ['HOME']
    logger.DEBUG("keep_running is %r" % keep_running)
    logger.DEBUG("ParseStartConfig for %s" % labname)
    is_valid_lab(lab_path)
    labtainer_config, start_config = GetBothConfigs(lab_path, logger, servers, clone_count)
    host_home_xfer = os.path.join(labtainer_config.host_home_xfer, labname)

    # Check existence of /home/$USER/$HOST_HOME_XFER directory - create if necessary
    host_xfer_dir = '%s/%s' % (myhomedir, host_home_xfer)
    CreateHostHomeXfer(host_xfer_dir)

    if DoStop(start_config, labtainer_config, lab_path, ignore_stop_error, run_container=run_container, 
              servers=servers, clone_count=clone_count, keep_running=keep_running):
        # Inform user where results are stored
        print "Results stored in directory: %s" % host_xfer_dir
    return host_xfer_dir

def DoMoreterm(lab_path, container_name, clone_num=None):
    labname = os.path.basename(lab_path)
    mycwd = os.getcwd()
    myhomedir = os.environ['HOME']
    logger.DEBUG("current working directory for %s" % mycwd)
    logger.DEBUG("current user's home directory for %s" % myhomedir)
    logger.DEBUG("ParseStartConfig for %s" % labname)
    is_valid_lab(lab_path)
    labtainer_config, start_config = GetBothConfigs(lab_path, logger)
    if container_name not in start_config.containers:
        print("Container %s not found. Container must be one of the following:" % container_name)
        for container_name in start_config.containers:
            print('\t%s' % container_name)
        print("Usage: moreterm.py <lab> <container>")
        exit(1)
        
    logger.DEBUG('num terms is %d' % start_config.containers[container_name].terminals)
    if clone_num is None:
        mycontainer_name = '%s.%s.student' % (labname, container_name)
    else:
        mycontainer_name = '%s.%s-%d.student' % (labname, container_name, clone_num)

    if not IsContainerCreated(mycontainer_name):
        logger.ERROR('container %s not found' % mycontainer_name)
        sys.exit(1)
    if not IsContainerRunning(mycontainer_name):
        logger.ERROR("Container %s is not running!\n" % (mycontainer_name))
        sys.exit(1)
    for x in range(1):
        # Change to allow spawning if terminal is 0 but not -1
	if start_config.containers[container_name].terminals == -1:
            print("No terminals supported for this component")
	    sys.exit(1)
	else:
            spawn_command = "gnome-terminal -- docker exec -it %s bash -l &" % 	mycontainer_name
	    logger.DEBUG("spawn_command is (%s)" % spawn_command)
	    os.system(spawn_command)

def DoTransfer(lab_path, container_name, filename, direction):
    '''TBD this is not tested and likey broken'''
    labname = os.path.basename(lab_path)
    mycwd = os.getcwd()
    myhomedir = os.environ['HOME']
    logger.DEBUG("current working directory for %s" % mycwd)
    logger.DEBUG("current user's home directory for %s" % myhomedir)
    logger.DEBUG("ParseStartConfig for %s" % labname)
    is_valid_lab(lab_path)
    labtainer_config, start_config = GetBothConfigs(lab_path, logger)
    host_home_xfer = os.path.join(labtainer_config.host_home_xfer, labname)
    logger.DEBUG('num terms is %d' % start_config.containers[container_name].terminals)
    host_xfer_dir = '%s/%s' % (myhomedir, host_home_xfer)

    mycontainer_name = '%s.%s.student' % (labname, container_name)
    if not IsContainerCreated(mycontainer_name):
        logger.ERROR('container %s not found' % mycontainer_name)
        sys.exit(1)
    if not IsContainerRunning(mycontainer_name):
        logger.ERROR("Container %s is not running!\n" % (mycontainer_name))
        sys.exit(1)
    container_user = ""
    for name, container in start_config.containers.items():
        if mycontainer_name == container.full_name:
            container_user = container.user

    if direction == "TOCONTAINER":
        # Transfer from host to container
        filename_path = '%s/%s' % (host_xfer_dir, filename)
        logger.DEBUG("File to transfer from host is (%s)" % filename_path)
        if os.path.exists(filename_path) and os.path.isfile(filename_path):
            # Copy file and chown it
            command = 'docker cp %s %s:/home/%s/' % (filename_path, mycontainer_name, container_user)
            logger.DEBUG("Command to execute is (%s)" % command)
            result = subprocess.call(command, shell=True)
            logger.DEBUG("Result of subprocess.call DoTransfer copy (TOCONTAINER) file (%s) is %s" % (filename_path, result))
            if result == FAILURE:
                logger.ERROR("Failed to copy file to container %s!\n" % mycontainer_name)
                sys.exit(1)
            command = 'docker exec %s sudo chown %s:%s /home/%s/%s' % (mycontainer_name, container_user, container_user, container_user, filename)
            logger.DEBUG("Command to execute is (%s)" % command)
            result = subprocess.call(command, shell=True)
            logger.DEBUG("Result of subprocess.call DoTransfer chown file (%s) is %s" % (filename_path, result))
            if result == FAILURE:
                logger.ERROR("Failed to set permission in container %s!\n" % mycontainer_name)
                sys.exit(1)
        else:
            logger.ERROR('Host does not have %s file' % filename_path)
	    sys.exit(1)
    else:
        # Transfer from container to host
        command = 'docker cp %s:/home/%s/%s %s/' % (mycontainer_name, container_user, filename, host_xfer_dir)
        logger.DEBUG("Command to execute is (%s)" % command)
        result = subprocess.call(command, shell=True)
        logger.DEBUG("Result of subprocess.call DoTransfer copy (TOHOST) file (%s) is %s" % (filename, result))
        if result == FAILURE:
            logger.ERROR("Failed to copy file from container %s!\n" % mycontainer_name)
            sys.exit(1)


def CopyFilesToHost(lab_path, container_name, full_container_name, container_user):
    labname = os.path.basename(lab_path)
    is_valid_lab(lab_path)
    config_path       = os.path.join(lab_path,"config") 
    copy_path = os.path.join(config_path,"files_to_host.config")
    logger.DEBUG('CopyFilesToHost %s %s %s' % (labname, container_name, full_container_name))
    logger.DEBUG('CopyFilesToHost copypath %s' % copy_path)
    if os.path.isfile(copy_path):
        with open(copy_path) as fh:
            for line in fh:
                if not line.strip().startswith('#'):
                    try:
                        os.mkdir(os.path.join(os.getcwd(), labname))
                    except OSError as e:
                        #logger.ERROR('could not mkdir %s in %s %s' % (labname, os.getcwd(),str(e)))
                        pass
                    container, file_name = line.split(':')                    
                    if container == container_name:
                        dest = os.path.join(os.getcwd(), labname, file_name)
                        command = 'docker cp %s:/home/%s/%s %s' % (full_container_name, container_user, 
                            file_name.strip(), dest)
                        logger.DEBUG("Command to execute is (%s)" % command)
                        result = subprocess.call(command, shell=True)
                        logger.DEBUG("Result of subprocess.call DoTransfer copy (TOHOST) file (%s) is %s" % (file_name, 
                            result))
                        if result == FAILURE:
                            logger.ERROR("Failed to copy file from container %s!\n" % full_container_name)
                            sys.exit(1)


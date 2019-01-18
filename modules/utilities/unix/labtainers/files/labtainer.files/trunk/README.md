Labtainers: A Docker-based cyber lab framework
==============================================

Labtainers include more than 45 cyber lab exercises and tools to build your own. Import a single VM appliance or install on a Linux system and your students are done with provisioning and administrative setup, for these and future lab exercises.

* Consistent lab execution environments and automated provisioning via Docker containers
* Multi-component network topologies on a modestly performing laptop computer 
* Automated assessment of student lab activity and progress
* Individualized lab exercises to discourage sharing solutions

Labtainers provide controlled and consistent execution environments in which students perform labs entirely within the confines of their computer, regardless of the Linux distribution and packages installed on the student's computer.  The only requirement is that the Linux system supports Docker.  See the [Papers][Papers] for additional information about the framework.
The Labtainers website, and downloads (including VM appliances with Labtainers pre-installed) are at <https://my.nps.edu/web/c3o/labtainers>.

[Papers]: https://my.nps.edu/web/c3o/labtainers#papers

Distribution created: 01/07/2019 14:03
Revision: v1.1-7859-g711b5d1 

## Content
[Distribution and Use](#distribution-and-use)

[Guide to directories](#guide-to-directories)

[Support](#support)

[Release notes](#release-notes)

## Distribution and Use
Labtainers was created by United States Government employees at 
The Center for Cybersecurity and Cyber Operations (C3O) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 

However, several of the labs are derived from SEED labs from 
Syracuse University, and include copyrighted and licensed elements
as set forth in their respective Lab Manuals.  These labs include:
bufoverflow, capabilities, formatstring, local-dns, onewayhash,
retlibc, setuid-env, sql-inject, tcpip, webtrack, xforge and xsite.

## Guide to directories

* scripts/labtainers-student -- the work directory for running and 
   testing student labs.  You must be in that directory to run 
   student labs.
   
* scripts/labtainers-instructor -- the work directory for 
   running and testing automated assessment and viewing student
   results.
  
* labs -- Files specific to each of the labs
   
* setup_scripts -- scripts for installing Labtainers and Docker and updating Labtainers
   
* docs -- latex source for the labdesigner.pdf, and other documentation.
   
* config -- system-wide configuration settings (these are not the 
   lab-specific configuration settings.
 
* distrib -- distribution support scripts, e.g., for publishing labs to the Docker hub.

* testsets -- Test procedures and expected results. (Per-lab drivers for SimSec are not 
distributed).

* pkg-mirrors -- utility scripts for internal NPS package mirroring to reduce external 
package pulling during tests and distribution.

## Support
Use the GitHub issue reports, or email me at mfthomps@nps.edu

Also see <https://my.nps.edu/web/c3o/support1> 


## Release notes

The standard Labtainers distribution does not include files required for development
of new labs.  For those, run ./update-designer.sh from the labtainer/trunk/setup\_scripts directory.

The installation script and the update-designer.sh script set environment variables,
so you may want to logout/login, or start a new bash shell before using Labtainers the
first time.
January 7, 2019
- Fix gdblesson automated assessment to at least be operational.

December 29, 2018
- Fix routing-basics2, same issues as routing-basics, plus an incorret ip address in the gateway resolv.conf

December 5, 2018
- Fix routing-basics lab, dns resolution at isp and gatway components was broken.

November 14, 2018
- Remove /run/nologin from archive machine in backups2 -- need general solution for this nologin issue

November, 5, 2018
- Change file-integrity lab default aid.conf to track metadata changes rather than file modification times

October 22, 2018
- macs-hash lab resolution verydodgy.com failed on lab restart
- Notify function failed if notify_cb.sh is missing

October 12, 2018
- Set ulimit on file size, limit to 1G

October 10, 2018
- Force collection of parameterized files
- Explicitly include leafpad and ghex in centos-xtra baseline and rebuild dependent images.

September 28, 2018
- Fix access modes of shared file in ACL lab
- Clarify question in pass-crack
- Modify artifact collection to ignore files older than start of lab.
- Add quantum computing algorithms lab

September 12, 2018
- Fix setuid-env grading syntax errors
- Fix syntax error in iptables2 example firewall rules
- Rebuild centos labs, move lamp derivatives to use lamp.xtr for waitparam and force
httpd to wait for that to finish.

September 7, 2018
- Add CyberCIEGE as a lab
- read\_pre.txt information display prior to pull of images, and chance to bail.

September 5, 2018
- Restore sakai bulk download processing to gradelab function.
- Remove unused instructor scripts.

September 4, 2018
- Allow multiple IP addresses per network interface
- Add base image for Wine
- Add GRFICS virtual ICS simulation

August 23, 2018
- Add GrassMarlin lab (ICS network discovery)

August 23, 2018
- Add GrassMarlin lab (ICS network discovery)

August 21, 2018
- Another fix around AWS authentication issues (DockerHub uses AWS).
- Fix new\_lab\_setup.py to use git instead of svn.
- Split plc-forensics lab into a basic lab and and advanced lab (plc-forensics-adv)

August 17, 2018
- Transition to git & GitHub as authoritative repo.

August 15, 2018
- Modify plc-forensics lab assessment to be more general; revise lab manual to reflect wireshark on the Labtainer.
 
August 15, 2018
- Add "checkwork" command allowing students to view automated assessment  results for their lab work.
- Include logging of iptables packet drops in the iptables2 and the iptables-ics lab.
- Remove obsolete instances of is\_true and is\_false from goal.config
- Fix boolean evaluation to handle "NOT foo", it had expected more operands.

August 9, 2018
- Support parameter replacement in results.config files
- Add TIME\_DELIM result type for results.config
- Rework the iptables lab, remove hidden nmap commands, introduce custom service

August 7, 2018
- Add link to student guide in labtainer-student directory
- Add link to student guide on VM desktops
- Fixes to iptables-ics to avoid long delay on shutdown; and fixes to regression tests
- Add note to guides suggesting student use of VM browser to transfer artifact zip file to instructor.

August 1, 2018
- Use a generic Docker image for automated assessment; stop creating "instructor" images per lab.

July 30, 2018
- Document need to unblock the waitparam.service (by creating flag directory)
if a fixlocal.sh script is to start a service for which waitparam is a
prerequisite.
- Add plc-app lab for PLC application firewall and whitelisting exercise.

July 25, 2018
- Add string\_contains operator to goals processing
- Modify assessment of formatstring lab to account for leaked secret not always being
at the end of the displayed string.

July 24, 2018
- Add SSH Agent lab (ssh-agent)

July 20, 2018
- Support offline building, optionally skip all image pulling
- Restore apt/yum repo restoration to Dockerfile templates.
- Handle redirect URL's from Docker registry blob retrieval to avoid 
authentication errors (Do not rely on curl --location).

July 12, 2018
- Add prestop feature to allow execution of designer-specified scripts on
selected components prior to lab shutdown.  
- Correct host naming in the ssl lab, it was breaking automated assessment.
- Fix dmz-lab initial state to permit DNS resolutions from inner network.
- FILE\REGEX processing was not properly handling multiline searches.
- Framework version derived from newly rebuilt images had incorrect default value.
 
July 10, 2018
- Add an LDAP lab
- Complete transition to systemd based Ubuntu images, remove unused files
- Move lab\_sys tar file to per-container tmp directory for concurrency.

July 6, 2018
- All Ubuntu base images replaced with versions based on systemd
- Labtainer container images in registry now tagged with base image ID & have labels reflecting 
the base image.
- A given installation will pull and use images that are consistent with the base images it possesses.
- If you are using a VM image,  you may want to replace that with a newer VM image from our website.
- New labs will not run without downloading newer base images; which can lead to your VM storing multiple
versions of large base images (> 500 MB each).
- Was losing artifacts from processes that were running when lab was stopped -- was not properly killing capinout
processes.

June 27, 2018
- Add support for Ubuntu systemd images
- Remove old copy of SimLab.py from labtainer-student/bin
- Move apt and yum sources to /var/tmp
- Clarify differences between use of "boolean" and "count\_greater" in assessments
- Extend Add-HOST in start.config to include all components on a network.
- Add option to new\_lab\_setup.py to add a container based on a copy of an existing container.

June 21, 2018
- Set DISPLAY env for root
- Fix to build dependency handling of svn status output
- Add radius lab
- Bug in SimLab append corrected
- Use svn, where appropriate, to change file names with new\_lab\_setup.py

June 19, 2018
- Retain order of containers defined in start.conf when creating terminal with multiple tabs
- Clarify designer manual to identify path to assessment configuration files.
- Remove prompt for instructor to provide email
- Botched error checking when testing for version number
- Include timestamps of lab starts and redos in the assessment json
- Add an SSL lab that includes bi-directional authentication and creation of certificates.

June 14, 2018
- Add diagnostics to parameterizing, track down why some install seem to fail on that.
- If a container is already created, make sure it is parameterized, otherwise bail to avoid corrupt or half-baked containers.
- Fix program version number to use svn HEAD

June 15, 2018
- Convert plain text instructions that appeared in xterms into pdf file.
- Fix bug in version handling of images that have not yet been pulled.
- Detect occurance of a container that was created, but not parameterized,
and prompt the user to restart the lab with the "-r" option.
- Add designer utility: rm\_svn.py so that removed files trigger an image rebuild.

June 13, 2018
- Install xterm on Ubuntu 18 systems
- Work around breakage in new versions of gnome-terminal tab handling

June 11, 2018
- Add version checking to compare images to the framework.
- Clarify various lab manuals

June 2, 2018
- When installing on Ubuntu 18, use docker.io instead of docker-ce
- The capinout caused a crash when a "sudo su" monitored command is followed by
a non-elevated user command.
- Move routing and resolv.conf settings into /etc/rc.local instead of fixlocal.sh
so they persist across start/stop of the containers.

May 31, 2018
- Work around Docker bug that caused text to wrap in a terminal without a line feed.
- Extend COMMAND\_COUNT to account for pipes
- Create new version of backups lab that includes backups to a remote server and 
backs up an entire partition.
- Alter sshlab instructions to use ssh-copy-id utility
- Delte /run/nologin file from parameterize.sh to permit ssh login on CentOS

May 30, 2018
- Extended new\_lab\_setup.py to permit identification of the base image to use
- Create new version of centos-log that includes centralized logging.
- Assessment validation was not accepting "time\_not\_during" option.
- Begin to integrate Labtainer Master for managing Labtainers from a Docker container.

May 25, 2018
- Remove 10 second sleeps from various services.  Was delaying xinetd responses, breaking
automated tests.
- Fix snort lab grading to only require "CONFIDENTIAL" in the alarm.  Remove unused
files from lab.
- Program finish times were not recorded if the program was running when the lab
was stopped.

May 21, 2018
- Fix retlibc grading to remove duplicate goal, was failing automated assessment
- Remove copies of mynotify.py from individual labs and lab template, it is 
has been part of lab\_sys/sbin, but had not been updated to reflect fixes made
for acl lab.

May 18, 2018
- Mask signal message from exec\_wrap so that segv error message looks right.
- The capinout was sometimes losing stdout, check command stdout on death of cmd.
- Fix grading of formatstring to catch segmentation fault message.
- Add type\_function feature to SimLab to type stdout of a script (see formatstring simlab). 
- Remove SimLab limitation on combining single/double quotes.
- Add window\_wait directive to SimLab to pause until window with given title
can be found.
- Modify plc lab to alter titles on physical world terminal to reflect status,
this also makes testing easier.
- Fix bufoverflow lab manual link.

May 15, 2018
- Add appendix on use of the SimLab tool to simulate user performance of labs for
regression testing and lab development.
- Add wait\_net function to SimLab to pause until selected network connections terminate.
- Change acl automated assessment to use FILE\_REGEX for multiline matching.
- SimLab test for xsite lab.

May 11, 2018
- Add "noskip" file to force collection of files otherwise found in home.tar, needed
for retrieving Firefox places.sqlite.
- Merge sqlite database with write ahead buffer before extracting.
- Corrections to lab manual for the symkeylab
- Grading additions for symkeylab and pubkey
- Improvements to simlab tool: support include, fix window naming.

May 9, 2018
- Fix parameterization of the file-deletion lab.  Correct error its lab manual.
- Replace use of shell=True in python scripts to reduce processes and allow tracking PIDs
- Clean up manuals for backups, pass-crack and macs-hash.

May 8, 2018
- Handle race condition to prevent gnome-terminal from executing its docker command
before an xterm instruction terminal runs its command.  
- Don't display errors when instuctor stops a lab started with "-d".
- Change grading of nmap-ssh to better reflect intent of the lab.
- Several document and script fixes suggested by olberger on github.

May 7, 2018
- Use C-based capinout program instead of the old capinout.sh to capture stdin and
stdout. See trunk/src-tool/capinout.  Removes limitations associated with use ctrl-C 
to break monitored programs and the display of passwords in telnet and ssh.
- Include support for saki bulk\_download zip processing to extract seperatly submitted
reports, and summarizes missing submits.
- Add checks to user-provided email to ensure they are printable characters.
- While grading, if user-supplied email does not match zip file name, proceed to grade
the results, but include note in the table reflecting *cheating*.  Require to recover from
cases where student enters garbage for an email address.
- Change telnetlab grading to not look at tcpdump output for passwords -- capinout fix
leads to correct character-at-a-time transmission to server.
- Fix typo in install-docker.sh and use sudo to alter docker dns setting in that script.

April 26, 2018
- Transition to use of "labtainer" to start lab, and "stoplab" to stop it.
- Add --version option to labtainer command.
- Add log\_ts and log\_range result types, and time\_not\_during goal operators.
Revamp the centos-log and sys-log grading to use these features.
- Put labsys.tar into /var/tmp instead of /tmp, sometimes would get deleted before expanded
- Running X applications as root fails after reboot of VM.
- Add "User Command" man pages to CentOS based labs
- Fix recent bug that prevented collection of docs files from students
- Modify smoke-tests to only compare student-specific result line, void of whitespace

April 20, 2018
- The denyhosts service fails to start the first time, moved start to student\_startup.sh.
- Move all faux\_init services until after parameterization -- rsyslog was failing to start
on second boot of container.
April 19, 2018
- The acl lab failed to properly assess performance of the trojan horse step.
- Collect student documents by default. 
- The denyhost lab changed to reflect that denyhosts (or tcp wrappers?) now
modifies iptables.  Also, the denyhosts service was failing to start on some occasions.
- When updating Labtainers, do not overwrite files that are newer than those
  in the archive -- preserve student lab reports.

April 12, 2018

- Add documentation for the purpose of lab goals, and display this for the instructor
   when the instructor starts a lab.
- Correct use of the precheck function when the program is in treataslocal, pass capintout.sh
   the full program path.
- Copy instr_config files at run time rather than during image build.
- Add Designer Guide section on debugging automated assessment.
- Incorrect case in lab report file names.
- Unncessary chown function caused instructor.py to sometimes crash.
- Support for automated testing of labs (see SimLab and smoketest).
- Move testsets and distrib under trunk
  
April 5, 2018

- Revise Firefox profile to remove "you've not use firefox in a while..." message.
- Remove unnessary pulls from registry -- get image dates via docker hub API instead.

March 28, 2018

- Use explicit tar instead of "docker cp" for system files (Docker does
   not follow links.) 
- Fix backups lab use separate file system and update the manual.

March 26, 2018

-  Support for multi-user modes (see Lab Designer User Guide).
-  Removed build dependency on the lab_bin and lab_sys files. Those are now copied
   during parameterization of the lab.
-  Move capinout.sh to /sbin so it can be found when running as root.

March 21, 2018

-  Add CLONE to permit multiple instances of the same container, e.g., for 
   labs shared by multiple concurrent students.
-  Adapt kali-test lab to provide example of macvlan and CLONE
-  Copy the capinout.sh script to /sbin so root can find it after a sudo su.

March 15, 2018

-  Support macvlan networks for communications with external hosts
-  Add a Kali linux base, and a Metasploitable 2 image (see kali-test)

March 8, 2018

-  Do not require labname when using stop.py
-  Catch errors caused by stray networks and advise user on a fix
-  Add support for use of local apt & yum repos at NPS

February 21, 2018

-  Add dmz-lab
-  Change "checklocal" to "precheck", reflecting it runs prior to the command.
-  Decouple inotify event reporting from use of precheck.sh, allow inotify
   event lists to include optional outputfile name.
-  Extend bash hook to root operations, flush that bash_history.
-  Allow parameterization of start.config fields, e.g., for random IP addresses
-  Support monitoring of services started via systemctl or /etc/init.d
-  Introduce time delimeter qualifiers to organize a timestamped log file into
   ranges delimited by some configuration change of interest (see dmz-lab)

February 5, 2018

-  Boolean values from results.config files are now treated as goal values
-  Add regular expression support for identifying artifact results.
-  Support for alternate Docker registries, including a local test registry for testing
-  Msc fixes to labs and lab manuals
-  The capinout monitoring hook was not killing child processes on exit.
-  Kill monitored processes before collecting artifacts
-  Add labtainer.wireshark as a baseline container, clean up dockerfiles

January 30, 2018

-  Add snort lab
-  Integrate log file timestamps, e.g., from syslogs, into timestamped results.
-  Remove undefined result values from intermediate timestamped json result files.
-  Alter the time_during goal assessment operation to associate timestamps with 
   the resulting goal value.

January 24, 2018

-  Use of tabbed windows caused instructor side to fail, use of double quotes.
-  Ignore files in \_tar directories (other than .tar) when determining build
   dependencies.


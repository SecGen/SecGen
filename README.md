Security Scenario Generator (SecGen)
==
This code is licensed under the GPL 3.0 license.

Summary
--

SecGen is a ruby application developed by Lewis Ardern for his Final Year Project that uses virtualization software to automatically create vulnerable virtual machines so students can learn security penetration testing techniques. 

Boxes like Metasploitable2 are always the same, this project uses Vagrant, Puppet, and Ruby to create vulnerable virtual machines quickly that can be used for learning or CTF events. 

Requirements
--
For now you will need to install the following:

Vagrant: http://www.vagrantup.com/

Virtual Box: https://www.virtualbox.org/

Ruby: https://www.ruby-lang.org/en/

Nokogiri: http://nokogiri.org/tutorials/installing_nokogiri.html

Puppet is not required on your local machine, the boxes that you use will need to have puppet installed on them the main box used has been from puppetlabs: http://puppet-vagrant-boxes.puppetlabs.com/debian-607-x64-vbox4210.box

Testing
--
While creatng this application I used the following:

	OSx Version 10.8.5
	Vagrant 1.5.0
	nokogiri (1.6.1)
	ruby 2.0.0p195 (2013-05-14 revision 40734) [x86_64-darwin12.5.0]
	basebox = puppettest - http://puppet-vagrant-boxes.puppetlabs.com/debian-607-x64-vbox4210.box
	VirtualBox 4.3.0

It should work on most linux distros but if there are any problems contact me.

Usage
--
ruby securitysimulator.rb -r  

This will create you a new project in /projects/Project and will create a Vagrant File / Report for you to view and see what has been installed, this will also give you a feel for how Vagrant spins up virtual machines. 

Puppet
--

mount/puppet/module 
contains all currently useable puppet module some self-created some taken from https://forge.puppetlabs.com/

mount/puppet/manifests
contains all the includes and modifications that are used to create vulnerabilities e.g 

include nfslewis::config 

which includes all of the class information of nfslewis and config.pp 

to learn more about puppet and understand the code check out http://puppetlabs.com/

Boxes
--
by default the 'system machines' are specified to boxes.xml you will need to modify this file to create a new system e.g. 

each system must be incremented by system3, system4, etc to work. Each vulnerability must match a type from vulns.xml or be blank or you will be returned an error. 

Networking
--
by default the networking is specified in networks.xml you will need to modify the range to you want. Each network is set to a range e.g:


You can modify this to whatever range you desire and vagrant will build it.

An example of how the program sets up the ip range for each system:

System1

    homeonly1 = 172.16.0.10 
	homeonly2 = 172.17.0.10 

System2 

	homeonly1 = 172.16.0.20 
	homeonly2 = 172.17.0.20  

The reason why is in lib/templates/vagrantbase.erb  it appends the system number along with a 0 at the end to remove the issue of system1 being on the .1 network.

Bases
--
Currently the only tested base is puppettest, however any debian system should work if it has puppet installed, you can add new bases to bases.xml by following the current structure. 

Vulnerabilities
--
Vulnerabilities are specified in vulns.xml, these are the 'useable' vulnerabilities currently, so when specifing vulnerabilities in boxes.xml you must use from this list or leave the name blank. current automated vulnerabilities are:
	
	ftp
	commandinjection
    nfs
    samba
    writeableshadow
    distcc
    ftpbackdoor
    sqlinjection

Kali
--
A Kali image is built with every project, this is very slow and can be tedious, if you already have your own hack lab then you can remove this from vagrantbase.erb, but you will need to modify your IP address so it is on the network range, or modify networks.xml.

Mount
--
the mount file contains all of the puppet information, ssh keys for the default kali image, along with files to be transfered during the installation phase, this is mounted to each machine but removed once the installation has completed.

Cleanup
--
After each system is installed, the systems will clean up after itself.

	Removes internet access to each host
	unmounting the /mount/
	clober files to all look like they were installed in 2006  
	change vagrant password 

Contributing
--
If you like the idea of SecGen, you are more than welcome to contribute to the project.

Contact
--
If you need to reach me my email is: lewisardern [at] live.co.uk

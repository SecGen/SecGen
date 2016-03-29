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

```bash
ruby secgen.rb <ARGS> 
```

SecGen accepts arguments to change the way that it behaves, the currently implemented arguments are:

```
   --run, -r: builds vagrant config and then builds the VMs
   --build-config, -c: builds vagrant config, but does not build VMs
   --build-vms, -v: builds VMs from previously generated vagrant config
   --help, -h: shows this usage information
```


Puppet
--

The puppet modules that are currently included can be found under the 'modules' directory.

Please see the wiki for guides on contributing modules to SecGen

to learn more about puppet and understand the code check out http://puppetlabs.com/

Bases
--
by default the 'system machines' are specified to bases.xml you will need to modify this file to create a new system e.g. 

each system must be incremented by system3, system4, etc to work.

Mount
--
When initialized, SecGen will bootstrap itself and move all currently implemented puppet modules into the 'mount' directory.

Contributing
--
If you like the idea of SecGen, you are more than welcome to contribute to the project, please see the wiki for guidance on how to contribute


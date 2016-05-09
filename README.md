# Security Scenario Generator (SecGen)

This code is licensed under the GPL 3.0 license.

## Summary

SecGen is a ruby application that uses virtualization software to automatically create vulnerable virtual machines so students can learn security penetration testing techniques. 

Boxes like Metasploitable2 are always the same, this project uses Vagrant, Puppet, and Ruby to create vulnerable virtual machines quickly that can be used for learning or CTF events. 

## Requirements
You will need to install the following:

Ruby: https://www.ruby-lang.org/en/
Vagrant: http://www.vagrantup.com/
Virtual Box: https://www.virtualbox.org/
Nokogiri: http://nokogiri.org/tutorials/installing_nokogiri.html
Puppet: http://puppet.com/

###On Ubuntu these commands should get you up and running
```bash
sudo apt-get install ruby-dev zlib1g-dev liblzma-dev build-essential patch vagrant virtualbox
gem install bundle
bundle install
```

## Usage

Basic usage:
```bash
ruby secgen.rb run
```
This will use the default scenario to randomly generate VM(s).

SecGen accepts arguments to change the way that it behaves, the currently implemented arguments are:

```bash
   ruby secgen.rb [--options] <command>

   OPTIONS:
   --scenario [xml file], -s [xml file]: set the scenario to use
              (defaults to #{SCENARIO_XML})
   --project [output dir], -p [output dir]: directory for the generated project
              (output will default to #{default_project_dir})
   --help, -h: shows this usage information

   COMMANDS:
   run, r: builds project and then builds the VMs
   build-project, p: builds project (vagrant and puppet config), but does not build VMs
   build-vms [/project/dir], v [project #]: builds VMs from a previously generated project
              (use in combination with --project [dir])

```

## Scenarios
## Modules
### Puppet

The puppet modules that are currently included can be found under the 'modules' directory.

Please see the wiki for guides on contributing modules to SecGen

to learn more about puppet and understand the code check out http://puppetlabs.com/

## Contributing
If you like the idea of SecGen, you are more than welcome to contribute to the project, please see the wiki for guidance on how to contribute

The SecGen team have prepared a VM located at: https://drive.google.com/open?id=0B6fyxD2qGmUIaXpDZElKczdQYW8 to make 
contributing for SecGen easier, it includes Ruby, git and RubyMine pre-installed, however, some tweaking may be required.

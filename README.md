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
````
sudo apt-get install ruby-dev zlib1g-dev liblzma-dev build-essential patch vagrant virtualbox
gem install bundle
bundle install
````

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
   build-vms, v: builds VMs from a previously generated project
              (use in combination with --project [dir])

```

## Scenarios
## Modules
### secgen_metadata.xml
#### requires
Any module can include in secgen_metadata.xml \<requires> tags to require other modules that satisfy a set of conditions are also in the scenario. When selecting a module, each dependency is resolved by checking if a module already satisfies the condition, in which case nothing happens, or a module that satisfies all the conditions is randomly selected and added to the scenario. This is recursive so a module can require modules that require modules. 

When conflicts occur (say for example, a previously selected module conflicts with all the valid options for resolving a dependency) the scenario is regenerated. This bruteforce approach is fairly effective, but <conflict> tags should be avoided wherever possible because they add complexity and reduce randomisation possibilities.

A module can have multiple \<requires>, each of which will ensure a single module fulfills all of the conditions, which are regexp matches against attributes.

For example, for a module that needs to have a repo refresh (apt-get update) first:
````  
  <requires>
    <type>update</type>
  </requires>
````
Or for a module that requires apache be installed by another module (rather than the module itself installing apache, alternatively):
````
<requires>
    <type>httpd</type>
    <software_name>apache</software_name>
  </requires>
````

In this (silly) example, writable_shadow requires apache which requires update:
![recursive_dependencies](https://cloud.githubusercontent.com/assets/670192/17168086/4a08e162-53d8-11e6-83a0-0892d4fc2d68.png)

In another silly example, here apache requires ftp, but all ftp modules conflict with writable_shadow: 
![recursive_dependency_resolution](https://cloud.githubusercontent.com/assets/670192/17168883/b18ff362-53dc-11e6-8288-fa49a5f3459e.png)

### Puppet

The puppet modules that are currently included can be found under the 'modules' directory.

Please see the wiki for guides on contributing modules to SecGen

to learn more about puppet and understand the code check out http://puppetlabs.com/

## Contributing
If you like the idea of SecGen, you are more than welcome to contribute to the project, please see the wiki for guidance on how to contribute

The SecGen team have prepared a VM located at: https://drive.google.com/open?id=0B6fyxD2qGmUIaXpDZElKczdQYW8 to make 
contributing for SecGen easier, it includes Ruby, git and RubyMine pre-installed, however, some tweaking may be required.

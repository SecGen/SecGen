# Security Simulator
#
# $Id$
#
# $Revision$
#
# This program allows you to use a large amount of virtual machines and install vulnerable software to create a learning environment.
#
# By: Lewis Ardern (Leeds Metropolitan University)

require 'mercenary'

require 'secgen/node'
require 'secgen/nodeset'
require 'secgen/puppet_manager'
require 'secgen/resource_manager'
require 'secgen/template'
require 'secgen/controller'
require 'secgen/cli'

# require 'secgen/system'
# require 'secgen/filecreator'
# require 'secgen/systemreader'
# require 'secgen/vagrant'

module Secgen
  VERSION="0.0.1"

  # Sets the Secgen configuration options.
  #
  # @example Set up options.
  #   Secgen.configure do |config|
  #     config.default_distro = "precise32"
  #   end
  #
  # @return [ Config ] The configuration object.
  #
  # @since 0.0.1
  def configure
    block_given? ? yield(Config) : Config
  end

  def dispatch(*argv)
    # forward commands to Secgen::CLI
  end
end

# TODO: Rewrite with mercenary
# TODO: Implement Secgen::CLI

# def usage
#   puts 'Usage:

#    run - creates virtual machines e.g run 10

#    kill - destoys current session

#    ssh - creates a ssh session for specifiec box e.g ssh box1

#    All options options are:
#    --help -h: show
#    --run -r: run
# '
#   exit
# end

# def run
# 	puts 'reading configuration file on how many virtual machines you want to create'

# 	puts 'creating vagrant file'
#   # uses nokogoiri to grab all the system information from boxes.xml
#   systems = SystemReader.new(BOXES_XML).systems

#    # create's vagrant file / report a starts the vagrant installation'
#   create_files = FileCreator.new(systems)
#   build_number = create_files.generate(systems)

#   vagrant = VagrantController.new
#   vagrant.vagrant_up(build_number)
# end

# def config
# 	usage
# end

# opts = GetoptLong.new(
#   [ '--help', '-h', GetoptLong::NO_ARGUMENT ],
#   [ '--run', '-r', GetoptLong::NO_ARGUMENT ],
#   [ '--config', '-c', GetoptLong::NO_ARGUMENT ]
# )

# opts.each do |opt, arg|
#   case opt
#     when '--help'
#       usage
#     when '--run'
#     	run
#     when '--config'
#     	#do a box count increment to next one
#     	#create template config file!
#     	config
#   end
# end

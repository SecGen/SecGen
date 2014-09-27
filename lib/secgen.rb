$:.unshift __dir__

require 'rubygems'
require 'bundler/setup'

require 'mercenary'
# require 'liquid'

module Secgen
  VERSION="0.0.2"

  autoload :ResourceManager, 'secgen/resource_manager'
  autoload :Template,        'secgen/template'
  autoload :Node,            'secgen/node'
  autoload :NodeSet,         'secgen/nodeset'

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
end

require 'secgen/command'
Dir["#{__dir__}/secgen/commands/*.rb"].each { |p| require p }

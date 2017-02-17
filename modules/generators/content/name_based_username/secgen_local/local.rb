#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'faker'

class NameBasedUsernameGenerator < StringEncoder
  attr_accessor :name

  def initialize
    super
    self.module_name = 'Name Based Username Generator'
    self.name = ''
  end

  # Generate a username based on a random adjective and a random noun
  def encode_all
    self.outputs << Faker::Internet.user_name(self.name, %w(- _))
  end

  def read_arguments
    # Get command line arguments
    opts = GetoptLong.new(
        ['--help', '-h', GetoptLong::NO_ARGUMENT],
        ['--name', GetoptLong::REQUIRED_ARGUMENT],
    )

    # process option arguments
    opts.each do |opt, arg|
      case opt
        when '--name'
          self.name << arg;
        else
          Print.err "Argument not valid: #{arg}"
          exit
      end
    end
  end

  def encoding_print_string
    'name: ' + self.name.to_s
  end
end

NameBasedUsernameGenerator.new.run
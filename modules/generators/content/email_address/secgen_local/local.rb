#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'faker'

class EmailAddressGenerator < StringEncoder
  attr_accessor :name

  def initialize
    super
    self.module_name = 'Email Address Generator'
    self.name = ''
  end

  def encode_all
    if self.name.empty?
      self.name = nil
    end
    self.outputs << Faker::Internet.email(self.name)
  end
end

def read_arguments
  # Get command line arguments
  opts = GetoptLong.new(
      ['--help', '-h', GetoptLong::NO_ARGUMENT],
      ['--name ', GetoptLong::OPTIONAL_ARGUMENT],
  )

  # process option arguments
  opts.each do |opt, arg|
    case opt
      when '--help'
        usage
      when '--name'
        self.name << arg;
      else
        Print.err "Argument not valid: #{arg}"
        usage
        exit
    end
  end
end

def encoding_print_string
  'name: ' + self.name.to_s
end

EmailAddressGenerator.new.run
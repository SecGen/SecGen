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
    self.outputs << Faker::Internet.user_name(self.name, %w(nil _))
  end

  def get_options_array
    super + [['--name', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    if opt == '--name'
      self.name << arg;
    end
  end

  def encoding_print_string
    'name: ' + self.name.to_s
  end
end

NameBasedUsernameGenerator.new.run
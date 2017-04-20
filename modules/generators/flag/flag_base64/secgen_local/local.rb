#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'
class Base64FlagGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Base64 Flag Generator'
  end

  def generate
    require 'securerandom'
    self.outputs << "flag{#{SecureRandom.base64}}"
  end
end

Base64FlagGenerator.new.run

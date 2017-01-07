#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'
require 'securerandom'

class StrongPasswordGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Strong Password Generator'
  end

  def generate
    self.outputs << SecureRandom.base64(15)
  end
end

StrongPasswordGenerator.new.run
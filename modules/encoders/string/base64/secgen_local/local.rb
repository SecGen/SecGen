#!/usr/bin/ruby
require 'base64'
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class BASE64Encoder < StringEncoder
  def initialize
    super
    self.module_name = 'BASE64 Encoder'
  end

  def encode
    self.output = Base64.strict_encode64(strings_to_encode)
  end
end

BASE64Encoder.new.run

#!/usr/bin/ruby
require 'bases'
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class BASE32Encoder < StringEncoder
  def initialize
    super
    self.module_name = 'BASE32 Encoder'
    self.strings_to_encode = ['test']
  end

  def encode(str)
    byte_array = str.bytes
    byte_array.each { |byte|
      Bases.val(byte).in_hex.to_base(64)
    }
    test = Bases.val(binary).in_base(10).to_base(64)

    test
  end
end

BASE32Encoder.new.run

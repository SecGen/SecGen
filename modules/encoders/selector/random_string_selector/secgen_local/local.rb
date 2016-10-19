#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class ROT13Encoder < StringEncoder

  def initialize
    super
    self.module_name = 'Random String Selector'
  end

  def encode_all
    self.outputs << strings_to_encode.sample
  end
end

ROT13Encoder.new.run

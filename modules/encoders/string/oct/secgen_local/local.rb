#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class OctalEncoder < StringEncoder
  def initialize
    super
    self.module_name = 'Octal Encoder'
  end

  def encode(str)
    encoded_char_array = []
    str.each_char { |char| encoded_char_array << char.ord.to_s(8) }
    encoded_char_array.join
  end
end

OctalEncoder.new.run

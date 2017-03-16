#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class DecimalEncoder < StringEncoder
  def initialize
    super
    self.module_name = 'Decimal Encoder'
  end

  def encode(str)
    encoded_char_array = []
    str.each_char { |char| encoded_char_array << char.ord.to_s }
    encoded_char_array.join
  end
end

DecimalEncoder.new.run

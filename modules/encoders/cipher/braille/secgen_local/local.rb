#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'braille'
require 'braille/translator'

class BrailleEncoder < StringEncoder

  def initialize
    super
    self.module_name = 'Braille Encoder'
    self.strings_to_encode = []
  end

  def encode(str)
    Braille::Translator.new.call(str)
  end
end

BrailleEncoder.new.run
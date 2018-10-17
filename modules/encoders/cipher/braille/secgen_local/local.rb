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
    braille = Braille::Translator.new
    translation = []
    str.each_char do |char|
      if ! char =~ /[a-zA-Z0-9]/   # If non-alphanumeric, return the character as is.
        translation << char
      else
        translation << braille.translate_word(char)
      end
    end
    translation.join
  end
end

BrailleEncoder.new.run
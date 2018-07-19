#!/usr/bin/ruby
require_relative 'local_string_encoder.rb'
require 'digest'

class HashEncoder < StringEncoder
  attr_accessor :salt
  attr_accessor :return_salts

  def initialize
    super
    self.module_name = 'Hash Encoder'
    self.strings_to_encode = []
    self.salt = []
    self.return_salts = false
  end

  def hash_function(str)
  end

  def encode_all
    self.strings_to_encode.each_with_index do |string, i|

      combined_string = string
      if self.salt[i]
        combined_string += self.salt[i]
      end

      self.outputs << hash_function(combined_string)
    end

    if self.return_salts
      self.outputs += self.salt
    end
  end

  def process_options(opt, arg)
    super
    if opt == '--salt'
      self.salt << arg;
    end

    if opt == '--return_salts'
      self.return_salts = (arg.to_s.downcase == 'true');
    end
  end


  def get_options_array
    super + [['--salt', GetoptLong::OPTIONAL_ARGUMENT],
             ['--return_salts', GetoptLong::OPTIONAL_ARGUMENT]]
  end

  def encoding_print_string
    'strings_to_encode: ' + self.strings_to_encode.to_s + print_string_padding +
    'salt: ' + self.salt.to_s + print_string_padding +
    'return_salts: ' + self.return_salts.to_s
  end
end
#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_hash_encoder.rb'

class LMEncoder < HashEncoder
  def initialize
    super
    self.module_name = 'LM Hash Encoder'
  end

  def hash_function(string)
    require 'smbhash'
    string_to_hash = string
    if string.length > 14
      string_to_hash = string[0..13]
    end
    Smbhash.lm_hash(string_to_hash)
  end
end

LMEncoder.new.run

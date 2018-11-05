#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_hash_encoder.rb'

class LMEncoder < HashEncoder
  def initialize
    super
    self.module_name = 'LM Hash Encoder'
  end

  def hash_function(string)
    require 'smbhash'
    Smbhash.lm_hash(string)
  end
end

LMEncoder.new.run

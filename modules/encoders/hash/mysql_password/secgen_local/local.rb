#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_hash_encoder.rb'

class MySQLPasswordHashEncoder < HashEncoder
  def initialize
    super
    self.module_name = 'MySQL Password Hash Encoder'
    self.strings_to_encode = ['right']
  end

  def hash_function(string)
    require 'digest/sha1'
    "*" + Digest::SHA1.hexdigest(Digest::SHA1.digest(string)).upcase
  end
end

MySQLPasswordHashEncoder.new.run

#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class WordGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Word Generator'
  end

  def generate
    file = File.readlines("#{ROOT_DIR}/lib/resources/wordlists/wordlist")
    self.outputs << 'flag{' + file.sample.chomp + file.sample.chomp + file.sample.chomp + file.sample.chomp + file.sample.chomp + '}'
  end
end

WordGenerator.new.run

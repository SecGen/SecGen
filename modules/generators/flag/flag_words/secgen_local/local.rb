#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class WordFlagGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Word Based Flag Generator'
  end

  def generate
    file = File.readlines("#{ROOT_DIR}/lib/resources/wordlists/wordlist")
    flag_string = file.sample.chomp + file.sample.chomp + file.sample.chomp + file.sample.chomp + file.sample.chomp
    flag_string.gsub!(/[^0-9a-z ]/i, '')  # strip special characters from the word string. removes umlauts/accents etc.
    self.outputs << 'flag{' + flag_string + '}'
  end
end

WordFlagGenerator.new.run

#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class WordGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Word Generator'
  end

  def generate
    # require 'wordlist'
    #
    # list = Wordlist::FlatFile.new("#{ROOT_DIR}/lib/resources/wordlists/wordlist")
    # list.each_unique do |word|
    #   outputs << word
    #   break
    # end

    self.outputs << File.readlines("#{ROOT_DIR}/lib/resources/wordlists/wordlist").sample.chomp

  end
end

WordGenerator.new.run
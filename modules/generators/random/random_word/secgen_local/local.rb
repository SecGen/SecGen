#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class WordGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Word Generator'
  end

  def generate
    self.outputs << File.readlines("#{WORDLISTS_DIR}/wordlist").sample.chomp
  end
end

WordGenerator.new.run
#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'
require_relative '../../../../../lib/helpers/blacklist.rb'

class WordGenerator < StringGenerator
  attr_accessor :wordlist

  def initialize
    super
    self.wordlist = []
    self.module_name = 'Random Word Generator'
  end

  def get_options_array
    super + [['--wordlist', GetoptLong::OPTIONAL_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    case opt
      when '--wordlist'
        self.wordlist << arg;
    end
  end

  def generate
    blacklist = Blacklist.new
    flag_word = ''

    until flag_word != ''
      selected_word = File.readlines("#{WORDLISTS_DIR}/#{self.wordlist.sample.chomp}").sample.chomp
      unless blacklist.is_blacklisted? selected_word
        flag_word = selected_word.gsub(/[^\w]/, '')
      end
    end

    self.outputs << flag_word
  end
end

WordGenerator.new.run
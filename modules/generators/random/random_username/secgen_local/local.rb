#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class WordGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Random Username Generator'
  end

  # Generate a username based on a random adjective and a random noun
  def generate
    # Load adjectives + nouns
    adjectives = File.readlines("#{WORDLISTS_DIR}/adjectives")
    nouns = File.readlines("#{WORDLISTS_DIR}/nouns")

    # Maximum length username: 20 characters
    max_username_length = 20
    username = ''

    suitable_username_generated = false
    until suitable_username_generated
      random_adjective = adjectives.sample.chomp
      filler_character = ['','_','-'].sample
      random_noun = nouns.sample.chomp

      proposed_username = random_adjective + filler_character + random_noun

      if proposed_username.length <= max_username_length
        suitable_username_generated = true
        username = proposed_username
      end
    end

    self.outputs << username
  end
end

WordGenerator.new.run
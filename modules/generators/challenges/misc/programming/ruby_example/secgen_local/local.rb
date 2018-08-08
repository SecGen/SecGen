#!/usr/bin/ruby

require_relative '../../../../../../../lib/objects/local_ruby_challenge_generator.rb'
class ExampleRubyScriptGenerator < RubyChallengeGenerator

  def initialize
    super
    self.module_name = 'Ruby Example Script Generator'
  end

  def challenge_content
    "puts File.read(flag_path)"
  end

end

ExampleRubyScriptGenerator.new.run
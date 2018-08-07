#!/usr/bin/ruby

require_relative 'local_script_challenge_generator.rb'
class RubyChallengeGenerator < ScriptChallengeGenerator

  def initialize
    super
    self.module_name = 'Ruby Example Script Generator'
  end

  def pre_challenge_setup
    "Dir.chdir(ARGV[0])\n"
  end

  def interpreter_path
    '/usr/bin/ruby'
  end

end
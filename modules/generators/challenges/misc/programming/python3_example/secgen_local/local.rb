#!/usr/bin/ruby

require_relative '../../../../../../../lib/objects/local_script_challenge_generator.rb'
class RubyExampleScriptGenerator < ScriptChallengeGenerator

  def initialize
    super
    self.module_name = 'Python3 Example Script Generator'
  end


  def interpreter_path
    '/usr/bin/python3'
  end

  def script_content
"from sys import argv
with open('flag') as f:
  print(f.read())"
  end

end

RubyExampleScriptGenerator.new.run
#!/usr/bin/ruby

require_relative '../../../../../../../lib/objects/local_ruby_challenge_generator.rb'
class EchoStringChallenge < RubyChallengeGenerator

  def initialize
    super
    self.module_name = 'Echo String Script Generator'
  end

  def challenge_content
    File.read(File.join(File.dirname(__FILE__), "#{difficulty}.rb"))
  end

end

EchoStringChallenge.new.run
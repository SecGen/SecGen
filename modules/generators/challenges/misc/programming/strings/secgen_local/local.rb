#!/usr/bin/ruby

require_relative '../../../../../../../lib/objects/local_ruby_challenge_generator.rb'
class EchoStringChallenge < RubyChallengeGenerator

  def initialize
    super
    self.module_name = 'Echo String Script Generator'
  end

  def randomise_by_difficulty
    __FILE__
  end

end

EchoStringChallenge.new.run
#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class RandomMetaCTFChallenge < StringGenerator
  attr_accessor :difficulty

  def initialize
    super
    self.module_name = 'Random Wordpress Version Generator'
    self.difficulty = 'easy'
  end

  def generate


    src_angr = [
        # TODO : Adjust the difficulties
        {:difficulty => 'easy', :name => '00_angr_find'},
        {:difficulty => 'easy', :name => '01_angr_avoid'},
        {:difficulty => 'easy', :name => '02_angr_find_condition'},
        {:difficulty => 'easy', :name => '03_angr_symbolic_registers'},
        {:difficulty => 'easy', :name => '04_angr_symbolic_stack'},
        {:difficulty => 'easy', :name => '05_angr_symbolic_memory'},
        {:difficulty => 'medium', :name => '06_angr_symbolic_dynamic_memory'},
        {:difficulty => 'medium', :name => '07_angr_symbolic_file'},
        {:difficulty => 'medium', :name => '08_angr_constraints'},
        {:difficulty => 'medium', :name => '09_angr_hooks'},
        {:difficulty => 'medium', :name => '10_angr_simprocedures'},
        {:difficulty => 'medium', :name => '11_angr_sim_scanf'},
        {:difficulty => 'medium', :name => '12_angr_veritesting'},
        {:difficulty => 'medium', :name => '13_angr_static_binary'},
        {:difficulty => 'hard', :name => '14_angr_shared_library'},
        {:difficulty => 'hard', :name => '15_angr_arbitrary_read'},
        {:difficulty => 'hard', :name => '16_angr_arbitrary_write'},
        {:difficulty => 'hard', :name => '17_angr_arbitrary_jump'},
    ]

    # TODO
    # src_csp = []
    # src_malware = []
    # challenges = src_angr + src_csp + src_malware

    challenges = src_angr


    # Select based on difficulty

    challenges.delete_if do |challenge|
        challenge[:difficulty] != self.difficulty
    end

    challenge = challenges.sample
    outputs << challenge[:name]

  end

end

RandomMetaCTFChallenge.new.run
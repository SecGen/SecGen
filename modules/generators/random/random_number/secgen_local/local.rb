#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class NumberGenerator < StringGenerator
  attr_accessor :minimum
  attr_accessor :maximum

  def initialize
    super
    self.module_name = 'Random NumberGenerator'
    self.minimum = 0
    self.maximum = 10
  end

  def generate
    self.outputs << rand(self.minimum .. self.maximum).to_s
  end

  def process_options(opt, arg)
    super
    if opt == '--minimum'
      self.minimum = arg.to_i;
    end

    if opt == '--maximum'
      self.maximum = arg.to_i;
    end
  end

  def get_options_array
    super + [['--minimum', GetoptLong::REQUIRED_ARGUMENT],
             ['--maximum', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def encoding_print_string
    'minimum: ' + self.minimum.to_s + print_string_padding +
    'maximum: ' + self.maximum.to_s
  end
end

NumberGenerator.new.run
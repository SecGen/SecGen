#!/usr/bin/ruby
require 'json'
require 'time'
require_relative '../../../../../lib/objects/local_string_encoder.rb'

class DateGenerator < StringEncoder
  attr_accessor :format

  def initialize
    super
    self.module_name = 'Date Generator'
    self.format = ''
  end

  def encode_all
    # Generate random date from epoch -> current time
    date = Time.at(rand * Time.now.to_i)

    if format == 'mysql_datetime'
      date = Time.now.strftime('%Y-%m-%d %H:%M:%S')
    end

    self.outputs << date
  end

  def get_options_array
    super + [['--format', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    case opt
      when '--format'
        self.format << arg;
    end
  end

  def encoding_print_string
    'format: ' + self.format.to_s
  end
end

DateGenerator.new.run

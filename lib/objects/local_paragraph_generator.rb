#!/usr/bin/ruby
require_relative 'local_string_encoder.rb'

class ParagraphGenerator < StringEncoder
  attr_accessor :paragraph_count

  def initialize
    super
    self.module_name = 'Paragraph Generator'
    self.paragraph_count = ''
  end

  def encode_all
    # Override me
  end

  def read_arguments
    # Get command line arguments
    opts = GetoptLong.new(
        ['--help', '-h', GetoptLong::NO_ARGUMENT],
        ['--paragraph_count', GetoptLong::REQUIRED_ARGUMENT],
    )

    # process option arguments
    opts.each do |opt, arg|
      case opt
        when '--help'
          usage
        when '--paragraph_count'
          self.paragraph_count << arg;
        else
          Print.err "Argument not valid: #{arg}"
          usage
          exit
      end
    end
  end

  def encoding_print_string
    'paragraph_count: ' + self.paragraph_count.to_s
  end
end
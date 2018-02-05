#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class RandomSelectorEncoder < StringEncoder
  attr_accessor :position
  attr_accessor :file_path

  def initialize
    super
    self.module_name = 'Random Line Selector'
    self.file_path = ''
    self.position = ''
  end

  def encode_all
    file_lines = File.readlines("#{ROOT_DIR}/#{file_path}")

    selected_string = if !position.nil? && (position != '')
                        file_lines[position.to_i - 1]
                      else
                        file_lines.sample
                      end
    outputs << selected_string
  end

  def process_options(opt, arg)
    super
    case opt
      # Removes any non-alphabet characters
    when '--position'
      position << arg
    when '--file_path'
      file_path << arg
    else
      # do nothing
    end
  end

  def get_options_array
    super + [['--position', GetoptLong::OPTIONAL_ARGUMENT],
             ['--file_path', GetoptLong::OPTIONAL_ARGUMENT]]
  end

  def encoding_print_string
    string = "file_path: #{file_path}"
    unless position.to_s.empty?
      string += print_string_padding + "position: #{position}"
    end
    string
  end

end

RandomSelectorEncoder.new.run

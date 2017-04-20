#!/usr/bin/ruby
require 'json'
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'faker'

class FilenameGenerator < StringEncoder
  attr_accessor :file_name
  attr_accessor :extension

  def initialize
    super
    self.module_name = 'Random Filename Generator'
    self.file_name = ''
    self.extension = ''
  end

  def encode_all
    file_name = self.file_name
    extension = self.extension

    if file_name.empty?
      file_name = nil
    end
    if extension.empty?
      extension = nil
    end

    if self.extension == 'no_extension'
      extension = ''
    end

    leaked_filenames = %w(top_secret_information secrets hush_hush private_stuff restricted classified confidential)
    15.times{ leaked_filenames << Faker::File.file_name('', file_name, extension, '').chomp('.') }

    output = leaked_filenames.sample

    self.outputs << output
  end

  def process_options(opt, arg)
    super
    case opt
      when '--file_name'
        self.extension << arg;
      when '--extension'
        self.extension << arg;
    end
  end

  def get_options_array
    super + [['--file_name', GetoptLong::OPTIONAL_ARGUMENT],
             ['--extension', GetoptLong::OPTIONAL_ARGUMENT]]
  end

  def encoding_print_string
    if self.file_name.empty? && self.extension.empty?
      'no args'
    else
      'file_name: ' + self.file_name.to_s + ',
    extension: ' + self.extension.to_s
    end
  end
end

FilenameGenerator.new.run

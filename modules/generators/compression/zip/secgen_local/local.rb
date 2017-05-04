#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'rubygems'
require 'zip'

class ZipFileGenerator < StringEncoder
  attr_accessor :file_name
  attr_accessor :strings_to_leak

  def initialize
    super
    self.module_name = 'Zip File Generator'
    self.file_name = ''
    self.strings_to_leak = []
  end

  def encode_all
    zip_file_path = GENERATORS_DIR + 'compression/zip/secgen_local/archive.zip'

    # Create a zip archive compressing a file containing strings_to_leak
    Zip::File.open(zip_file_path, Zip::File::CREATE) do |zip_file|
      zip_file.get_output_stream(self.file_name) { |os|
        os.write self.strings_to_leak.join("\n")
      }
    end

    # Read zip file contents into memory & delete the archive.zip from disk
    file_contents = File.binread(zip_file_path)
    FileUtils.rm(zip_file_path)

    self.outputs << Base64.strict_encode64(file_contents)
  end

  def get_options_array
    super + [['--file_name', GetoptLong::REQUIRED_ARGUMENT],
             ['--strings_to_leak', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    case opt
      when '--file_name'
        self.file_name << arg;
      when '--strings_to_leak'
        self.strings_to_leak << arg;
    end
  end

  def encoding_print_string
    'file_name: ' + self.file_name.to_s +
    'file_contents: ' + self.strings_to_leak.to_s
  end
end

ZipFileGenerator.new.run
#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'huffman'

class HuffmanEncoder < StringEncoder
  attr_accessor :subdirectory

  def initialize
    super
    self.module_name = 'Huffman Encoder'
    self.subdirectory = ''
    Dir.mkdir '../tmp/' unless Dir.exists? '../tmp/'
  end

  def encode_all
    tree_path = "../tmp/tree"
    result = Huffman.encode_text(strings_to_encode[0], tree_picture: true, tree_path: tree_path)

    self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(result.first), :filename => 'cipher', :ext => 'txt', :subdirectory => self.subdirectory}}.to_json
    self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(File.binread("#{tree_path}.png")), :filename => 'tree', :ext => 'png', :subdirectory => self.subdirectory}}.to_json
  end

  def process_options(opt, arg)
    super
    case opt
      # Removes any non-alphabet characters
      when '--subdirectory'
        self.subdirectory << arg;
    end
  end

  def get_options_array
    super + [['--subdirectory', GetoptLong::REQUIRED_ARGUMENT]]
  end


  def encoding_print_string
    'strings_to_encode: ' + self.strings_to_encode.to_s + print_string_padding +
    'subdirectory: ' + self.subdirectory.to_s
  end
end

HuffmanEncoder.new.run
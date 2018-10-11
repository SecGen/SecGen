#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'huffman'

class HuffmanEncoder < StringEncoder

  def initialize
    super
    self.module_name = 'Huffman Encoder'
    self.strings_to_encode = []
    Dir.mkdir '../tmp/' unless Dir.exists? '../tmp/'
  end

  def encode_all
    tree_path = "../tmp/tree"
    result = Huffman.encode_text(strings_to_encode[0], tree_picture: true, tree_path: tree_path)

    self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(result.first), :filename => 'cipher', :ext => 'txt', :subdir => ''}}.to_json
    self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(File.binread("#{tree_path}.png")), :filename => 'tree', :ext => 'png', :subdir => ''}}.to_json
  end
end

HuffmanEncoder.new.run
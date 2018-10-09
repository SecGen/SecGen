#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'huffman'

class HuffmanEncoder < StringEncoder
  attr_accessor :index

  def initialize
    super
    self.module_name = 'Huffman Encoder'
    self.strings_to_encode = []
    self.index = 0
    Dir.mkdir '../tmp/' unless Dir.exists? '../tmp/'
  end

  def encode(str)
    tree_path = "../tmp/tree_#{index}"
    result = Huffman.encode_text(str, tree_picture: true, tree_path: tree_path)
    self.index += 1
    # Return an array with the compressed string, and the tree png encoded in base64
    [result.first, Base64.strict_encode64(File.binread("#{tree_path}.png"))]
  end
end

HuffmanEncoder.new.run
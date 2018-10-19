#!/usr/bin/ruby
# Inputs:       An ASCII string to mask
# Outputs:      2 bit stream strings which, when XOR'd together, result in the input string.
# Description:  Input string is converted into a bit stream (String A)
#               Random equivalent length bit stream is generated (String B)
#               Strings A and B are XOR'd together to give us a third (String C)
#               Strings B and C are returned.

require_relative '../../../../../lib/objects/local_string_encoder.rb'
class BitwiseXORChallengeGenerator < StringEncoder
  attr_accessor :string_to_mask

  def initialize
    super
    self.module_name = 'Bitwise XOR Challenge Generator'
    self.string_to_mask = ''
  end

  def encode_all
    number_of_bytes = self.string_to_mask.length

    # String A: Convert input that we're hiding into binary
    binary_string_to_mask = self.string_to_mask.unpack('B*')[0]

    # String B: Generate bitstream
    generated_bit_stream = []
    number_of_bytes.times do
      generated_bit_stream << (1..8).map { [0, 1].sample }.join
    end
    generated_bit_stream = generated_bit_stream.join

    # bitwise xor
    decimal_result = binary_string_to_mask.to_i(2) ^ generated_bit_stream.to_i(2)

    # Turn decimal result back into a string of bits
    binary_string_c = decimal_result.to_s(2)

    # prepend leading 0's to the result
    result = binary_string_c.to_s.rjust(number_of_bytes * 8, '0')

    # join the binary strings with an underscore
    self.outputs << "#{generated_bit_stream}_#{result}"
  end

  def get_options_array
    super + [['--string_to_mask', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    case opt
      when '--string_to_mask'
        self.string_to_mask << arg;
    end
  end

  def encoding_print_string
    'String to mask: ' + self.string_to_mask
  end
end

BitwiseXORChallengeGenerator.new.run
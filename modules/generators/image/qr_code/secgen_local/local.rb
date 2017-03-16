#!/usr/bin/ruby
require 'rqrcode'
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class QRCodeGenerator < StringEncoder
  attr_accessor :string_to_mask

  def initialize
    super
    self.module_name = 'QR Code Generator'
    self.string_to_mask = []
  end

  def encode_all
    qr_code = RQRCode::QRCode.new(self.string_to_mask[0])
    image = qr_code.as_png
    self.outputs <<  Base64.strict_encode64(image.to_blob)
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
    'String to mask: ' + self.string_to_mask.first
  end
end

QRCodeGenerator.new.run
require 'getoptlong'
require_relative '../helpers/constants'
require 'json'

# Inherited by local string encoders
# stdout used to return value
# use Print.local to print status messages (formatted to stdout)

# A nice side-effect is that each of these modules is also an executable script

class StringEncoder
  require_relative '../helpers/print.rb'

  attr_accessor :module_name
  attr_accessor :strings_to_encode
  attr_accessor :outputs

  # override this
  def initialize
    # default values
    self.strings_to_encode = []
    self.module_name = 'Null encoder'
    self.outputs = []
  end

  # override this
  def encode (str)
    str.tr!('A-Za-z', 'N-ZA-Mn-za-m')
  end

  def encode_all
    self.strings_to_encode.each do |value|
      self.outputs << encode(value)
    end
  end

  def read_arguments
    # Get command line arguments
    opts = GetoptLong.new(
        [ '--help', '-h', GetoptLong::NO_ARGUMENT ],
        [ '--strings_to_encode', '-s', GetoptLong::REQUIRED_ARGUMENT ]
    )

    # process option arguments
    opts.each do |opt, arg|
      case opt
        when '--help'
          usage
        when '--strings_to_encode'
          self.strings_to_encode << arg;
        else
          Print.err "Argument not valid: #{arg}"
          usage
          exit
      end
    end
  end

  def usage
    Print.err "Usage:
   #{$0} [--options]

   OPTIONS:
     --strings_to_encode [string]
"
    exit
  end

  def run
    Print.local module_name

    read_arguments

    Print.local_verbose "Encoding '#{self.strings_to_encode.to_s}'"
    encode_all
    Print.local_verbose "Encoded: #{self.outputs.to_s}"
    puts JSON.generate self.outputs
  end
end


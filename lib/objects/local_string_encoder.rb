require 'getoptlong'

# Inherited by local string encoders
# stdout used to return value
# use Print.local to print status messages (formatted to stdout)

class StringEncoder
  require_relative '../helpers/print.rb'

  attr_accessor :module_name
  attr_accessor :strings_to_encode
  attr_accessor :output

  # override this
  def initialize
    # default values
    self.strings_to_encode = 'Nothing to see here, move along'
    self.module_name = 'Null encoder'
  end

  # override this
  def encode
    self.output = strings_to_encode.tr!('A-Za-z', 'N-ZA-Mn-za-m')
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
          self.strings_to_encode = arg;
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

    Print.local_verbose "Encoding '#{self.strings_to_encode}'"
    encode
    Print.local_verbose "Encoded: #{self.output}"
    puts self.output
  end
end


require 'getoptlong'
require_relative '../helpers/constants'
require 'json'

# Inherited by local string generators
# stdout used to return value
# use Print.local to print status messages (formatted to stdout)

# A nice side-effect is that each of these modules is also an executable script

class StringGenerator
  require_relative '../helpers/print.rb'

  attr_accessor :module_name
  attr_accessor :outputs

  # override this
  def initialize
    # default values
    self.module_name = 'Null generator'
    self.outputs = []
  end

  # override this
  def generate
    self.outputs << ''
  end

  def read_arguments
    # Get command line arguments
    opts = GetoptLong.new(
        [ '--help', '-h', GetoptLong::NO_ARGUMENT ],
    )

    # process option arguments
    opts.each do |opt, arg|
      case opt
        when '--help'
          usage
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

    Print.local_verbose "Generating..."
    generate
    Print.local_verbose "Generated: #{self.outputs.to_s}"
    puts JSON.generate self.outputs
  end
end


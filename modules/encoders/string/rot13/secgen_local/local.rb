#!/usr/bin/ruby
require 'getoptlong'

# TODO: write status messages to stderr, output to stdout
$stderr.puts "TESTsss"

# TODO: maybe?? load/require so we can pass variables into methods???


# at least one argument
if ARGV.length < 1
  Print.err 'Missing argument'
  usage
  exit
end

# ARGV.each do|a|
#   puts "Argument: #{a}"
# end

# TODO: make all arguments optional?
# Get command line arguments
opts = GetoptLong.new(
    [ '--help', '-h', GetoptLong::NO_ARGUMENT ],
    [ '--strings_to_encode', '-s', GetoptLong::REQUIRED_ARGUMENT ]
)

strings_to_encode = 'Hello there'

# process option arguments
opts.each do |opt, arg|
  case opt
    when '--help'
      usage
    when '--strings_to_encode'
      strings_to_encode = arg;
    else
      Print.err "Argument not valid: #{arg}"
      usage
      exit
  end
end



puts strings_to_encode.tr!("A-Za-z", "N-ZA-Mn-za-m")

# TODO
def usage
  Print.std "Usage:
   #{$0} [--options]

   OPTIONS:
"
  exit
end


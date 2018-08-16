# #####################
# Programming Challenge
# Echo string - Medium: Reverse a string and repeat it back to the script
# #####################

require 'securerandom'
require 'timeout'

$stdout.sync = true  # Disable stdout caching (for challenges that can be run over the network)

# Should the challenge types be separate scripts (medium1,medium2,medium3 etc?)
# so the challenge type is static on the box? less difficult + more replayable
# TODO: Implement other medium difficulty options:
# in reverse [done]
# but I only want every second character starting with index 0 e.g. [0,2,4...]
# but I only want every second character starting with index 1 e.g. [1,3,5...]

puts 'Prepare yourself. You need to work quickly for this challenge.'
sleep 2
puts 'Echo the string back to me, in reverse, before the script times out. Try writing a script of your own to make it easier!'
sleep 2
puts 'Get ready, here we go...'
sleep 2
# Generate random string
string = [SecureRandom.base64(rand(20..40)), SecureRandom.hex(rand(20..40))].sample

puts string
STDOUT.flush

begin
  Timeout.timeout 0.3 do
    response = gets.chomp
    if response == string.reverse
      puts File.read(flag_path)  # Flag path
    else
      puts 'Incorrect answer!'
      exit
    end
  end
rescue Timeout::Error
  puts 'Too slow!'
  exit
end


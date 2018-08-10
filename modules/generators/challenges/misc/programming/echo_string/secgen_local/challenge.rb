require 'securerandom'
require 'timeout'

puts 'Prepare yourself. You need to work quickly for this challenge.'
sleep 2
puts 'Echo the string back to me before the script times out. Try writing a script of your own to make it easier!'
sleep 2
puts 'Get ready, here we go...'
sleep 2

# Generate random string
string = [SecureRandom.base64(rand(20..40)), SecureRandom.hex(rand(20..40))].sample

puts string

begin
  Timeout.timeout 0.25 do
    response = gets.chomp
    if response == string
      puts File.read('flag')
    else
      puts 'Incorrect answer!'
      exit
    end
  end
rescue Timeout::Error
  puts 'Too slow!'
  exit
end


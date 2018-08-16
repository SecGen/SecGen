require 'securerandom'
require 'timeout'

$stdout.sync = true # Disable stdout caching (for challenges that can be run over the network)

# determine encoding format required
encoding_formats = %w[reverse hex_little_endian hex_big_endian binary_little_endian binary_big_endian base64 octal decimal]
encoding_format = encoding_formats.sample

case encoding_format
  when 'reverse'
    print_string = 'reverse'
    operation = ->(data) {data.reverse}
  when 'hex_little_endian'
    print_string = 'hexadecimal (little endian / LSB first)'
    operation = ->(data) {data.unpack('h*').first}
  when 'hex_big_endian'
    print_string = 'hexadecimal (big endian / MSB first)'
    operation = ->(data) {data.unpack('H*').first}
  when 'binary_little_endian'
    print_string = 'binary (little endian / LSB first)'
    operation = ->(data) {data.unpack('b*').first}
  when 'binary_big_endian'
    print_string = 'binary (big endian / MSB first)'
    operation = ->(data) {data.unpack('B*').first}
  when 'base64'
    require 'base64'
    print_string = 'base64'
    operation = ->(data) {Base64.strict_encode64(data)}
  when 'octal'
    print_string = 'octal'
    operation = -> (data) {
      sum = []
      data.each_char {|char| sum << char.ord.to_s(8).to_i}
      sum.join
    }
  when 'decimal'
    print_string = 'decimal'
    operation = -> (data) {
      sum = []
      data.each_char {|char| sum << char.ord.to_s}
      sum.join
    }
  else
    print_string = 'ERROR'
    operation = ->(data) {data}
end

puts 'Prepare yourself. You need to work quickly for this challenge.'
sleep 2
puts "Echo the string back to me, in #{print_string}, before the script times out. Try writing a script of your own to make it easier!"
sleep 2
puts 'Get ready, here we go...'
sleep 2

# Generate random string
string = [SecureRandom.base64(rand(20..40)), SecureRandom.hex(rand(20..40))].sample
puts string

begin
  Timeout.timeout 0.3 do
    response = gets.chomp
    valid_answer = operation.call(string)
    if response == valid_answer
      puts File.read(flag_path)
    else
      puts 'Incorrect!'
      puts 'We were looking for: ' + valid_answer
      exit
    end
  end
rescue Timeout::Error
  puts 'Too slow!'
  exit
end

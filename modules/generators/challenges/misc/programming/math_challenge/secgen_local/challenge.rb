#!/usr/bin/ruby
require 'random'
require 'timeout'

# Valid operations
operations = {'add' => '+', 'subtract' => '-'}

# Types - pick a type first + store that, then select random from type
odd_even = ['odd', 'even']
begins_with = "begins with #{range(0..9)}"
ends_with = "ends with #{range(0..9)}"

type = odd_even # +



puts 'This is an arithmetic test. Time for some quick maths.'
sleep(0.5)
puts "Unfortunately our test is not quite as simple as 2 plus 2 that's 4, minus 1 that's 3."
sleep(0.5)
puts "This time you need to #{operation} every number that: #{type}."
sleep (0.5)
puts 'Here we go....'
sleep(0.5)

# Calculate

begin
  Timeout::timeout 5 do
    answer = gets.chomp
  end
rescue Timeout::Error
  puts 'Too slow!'
  exit
end

puts File.read(flag_path)
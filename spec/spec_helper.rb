require 'factory_girl'
require 'rspec/its'

RSpec.configure do |c|
  c.include FactoryGirl::Syntax::Methods
  c.tty = true
end

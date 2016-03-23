source 'https://rubygems.org'

puppetversion = ENV['PUPPET_VERSION']

is_ruby18 = RUBY_VERSION.start_with? '1.8'

if is_ruby18
  gem 'rspec', "~> 3.1.0",   :require => false
end
gem 'puppet', puppetversion, :require => false
gem 'puppet-lint'
gem 'puppetlabs_spec_helper', '>= 0.1.0'
gem 'rspec-puppet'
gem 'metadata-json-lint'

group :development do
  gem 'puppet-blacksmith'
end

source "https://rubygems.org"

group :development do
  gem "beaker", "> 2.0.0"
  gem "beaker-rspec", ">= 5.1.0"
  gem "pry"
  gem "puppet-blacksmith"
  gem "serverspec"
  gem "vagrant-wrapper"
  gem "metadata-json-lint"
end

group :test do
  gem "rake"
  gem "puppet", ENV['PUPPET_VERSION'] || '~> 3.7.0'
  gem "puppet-lint", :github => 'rodjek/puppet-lint',
    :ref => '2546fed6be894bbcff15c3f48d4b6f6bc15d94d1'

  # Pin for 1.8.7 compatibility for now
  gem "rspec", '< 3.2.0'
  gem "rspec-core", "3.1.7"
  gem "rspec-puppet", "~> 2.1"

  gem "puppet-syntax"
  gem "puppetlabs_spec_helper"
end

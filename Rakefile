require 'rubygems'
require 'bundler/setup'
require 'yaml'

$LOAD_PATH.unshift File.expand_path("../lib", __FILE__)
# require 'secgen'

task :default => :spec

require 'rspec/core/rake_task'
RSpec::Core::RakeTask.new(:spec) do |t|
  t.verbose = false
  t.rspec_opts = ['--require spec_helper', '--color', "--format=documentation"]
end

desc "Open an irb session preloaded with this library"
task :console do
  sh "irb -rubygems -r ./lib/secgen.rb"
end

# task :build do
#   sh "gem build sesi.gemspec"
# end

# task :release => :build do
#   sh "gem push sesi-#{sesi::VERSION}"
# end

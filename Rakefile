$LOAD_PATH.unshift File.expand_path("../lib", __FILE__)

require 'rubygems'

require 'rspec/core/rake_task'
RSpec::Core::RakeTask.new(:spec) do |t|
  t.pattern = 'spec/*_spec.rb'
  t.rspec_opts = ["--color"]
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

require 'rubygems'
require 'bundler/setup'

$:.unshift File.expand_path("../lib", __FILE__)

task :default => :spec

require 'rspec/core/rake_task'
RSpec::Core::RakeTask.new(:spec) do |t|
  t.verbose = false
end

desc "Open an irb session preloaded with this library"
task :console do
  sh "irb -rubygems -r ./lib/secgen.rb"
end

task :build do
  sh "gem build secgen.gemspec"
end

task :install => :build do
  sh "sudo gem install secgen-#{Secgen::VERSION}.gem"
end

task :release => :build do
  sh "git tag -a v#{Secgen::VERSION} -m 'Tagging #{Secgen::VERSION}'"
  sh "git push --tags"
  sh "gem push secgen-#{Secgen::VERSION}"
  sh "rm secgen-#{Secgen::VERSION}"
end

task :tags do
  sh 'ctags -e -R -f TAGS --exclude=.git --languages=ruby lib spec/support'
  Gem.loaded_specs.values.map do |g|
    sh "ctags -e -a -f TAGS -R --languages=ruby #{g.full_gem_path}"
  end
end

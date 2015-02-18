require 'bundler/setup'

$:.unshift File.expand_path('../lib', __FILE__)

task :default => :spec

require 'rspec/core/rake_task'
RSpec::Core::RakeTask.new(:spec) { |t| t.verbose = false }

desc 'Open pry session preloaded with this library'
task :console do
  require 'pry'
  require 'secgen'
  ARGV.clear
  Pry.start
end

task :build do
  sh 'gem build secgen.gemspec'
end

task :install => :build do
  sh "sudo gem install secgen-#{Secgen::VERSION}.gem"
end

task :release => :build do
  sh "git tag -a v#{Secgen::VERSION} -m 'Tagging #{Secgen::VERSION}'"
  sh 'git push --tags'
  sh "gem push secgen-#{Secgen::VERSION}"
  sh "rm secgen-#{Secgen::VERSION}"
end

task :tags do
  sh "bundle exec gem ripper_tags --reindex"
  sh "bundle exec ripper-tags -R -f TAGS --exclude=vendor"
end

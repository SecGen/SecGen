task :default => ["yard"]

desc "Generate_yard_documentation"
task :yard do
  require 'yard'
  require_relative '../../lib/constants.rb'

  YARD::Rake::YardocTask.new do |t|
    t.files   = ['../../README.md', '../../lib']   # optional
    t.options = ["--title=SecGen #{VERSION_NUMBER} Documentation", '--extra', '--opts'] # optional
    t.stats_options = ['--list-undoc']         # optional
  end
end

task :yard_clean do
  # NEED TO FIND A BETTER WAY TO CLEAN FILES AS VULNERABILITIES IN 'rm_rf'
  rm_rf('doc')
end

# YARD::Templates::Engine.generate
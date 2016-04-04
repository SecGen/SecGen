task :default => ["rdoc"]

require 'rdoc'
require_relative '../../lib/constants.rb'

RDoc::Task.new :rdoc do |rdoc|

  rdoc.main = "README.rdoc"
  #
  # rdoc.rdoc_files.include("README.md", "doc/*.rdoc", "app/**/*.rb", "lib/**/*.rb", "config/**/*.rb")
  #
  rdoc.title = "SecGen #{VERSION_NUMBER} Documentation"
  # rdoc.options << "--all"
  # rdoc.options << "--line-numbers"
  # rdoc.markup = "tomdoc"
  rdoc.rdoc_dir = "doc"
  #
  # rdoc.main = "README.doc"
  rdoc.rdoc_files.include("../../lib   *.rb")
  rdoc.options << "--all"
end
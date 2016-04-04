# require_relative '../lib/constants'
# require 'rdoc/rdoc'

# # options = RDoc::Options.new
# # see RDoc::Options
#
# rdoc = RDoc::RDoc.new
#
# # rdoc.gather_files('lib/*.rb')
# # rdoc.parse_files('lib/*.rb')
# # rdoc.setup_output_dir(doc,true)
# # rdoc.update_output_dir
# options = rdoc.load_options
#
# rdoc.document options
# # see RDoc::RDoc

# rdoc = RDoc::RDoc.new
# rdoc.document %w[--include=DIRECTORIES lib/*.rb --output doc]

# rdoc = RDoc::RDoc.new
# rdoc.document %w[--include=DIRECTORIES lib/*.rb]

require 'rdoc'
require_relative '../../lib/constants.rb'

options = RDoc::Options.new
options.title = "SecGen #{VERSION_NUMBER} Documentation"
options.op_dir = 'doc'
options.main_page = 'README.rdoc'
options.files = %w[../../lib]
options.setup_generator 'darkfish'

RDoc::RDoc.new.document options
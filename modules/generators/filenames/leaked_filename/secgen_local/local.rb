#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'
class LeakedFilenameGenerator < StringGenerator
  def initialize
    super
    self.module_name = 'Leaked Filename Generator'
  end

  def generate
    leaked_filenames = %w(top_secret_information secrets hush_hush private_stuff restricted classified confidential)
    self.outputs << leaked_filenames.sample
  end
end

LeakedFilenameGenerator.new.run
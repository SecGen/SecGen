#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'faker'

class WebsiteThemeGenerator < StringEncoder

  def initialize
    super
    self.module_name = 'Website Theme Generator'
  end

  # Selects one of the parameterised_website themes and returns it
  def encode_all
    filenames = Dir.entries("#{ROOT_DIR}/modules/services/unix/http/parameterised_website/files/themes/").reject {|f| File.directory?(f) || f[0].include?('.')}
    self.outputs << filenames.sample
  end

end

WebsiteThemeGenerator.new.run
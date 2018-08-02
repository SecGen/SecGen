#!/usr/bin/ruby

require_relative '../../../../../../../lib/objects/local_string_generator.rb'
class RubyExampleScriptGenerator < StringGenerator

  def initialize
    super
    self.module_name = 'Ruby Example Script Generator'
  end


  def generate
    self.outputs << "#!/usr/local/bin/suid /usr/bin/ruby --
      puts File.read('flag')"
  end

end

RubyExampleScriptGenerator.new.run
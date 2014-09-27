module Secgen
  module Commands

    class Add < Command
      class << self
        def register_command(p)
          p.command(":add") do |c|
            c.syntax 'new [node-name]'
            c.description 'Creates a new, blank node'

            c.option 'base', '-b', '--base', 'Explicitly specify a distro'
            c.option 'network', '-n', '--network', 'What network to assign this node to'

            c.action do |args, opts|
              Secgen::Commands::Add.process(options)
            end
          end
        end

        def process(options)
          # ...
        end
      end
    end

  end
end

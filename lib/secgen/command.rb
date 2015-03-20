module Secgen
  class Command

    # A trick Jekyll uses to keep track of all the classes that
    # inherit Command so that the binary can easily import and
    # register them all with Mercenary.
    class << self
      def subclasses
        @subclasses ||= []
      end

      def inherited(base)
        subclasses << base
        super(base)
      end

      def register_command(p)
      end
    end
  end
end

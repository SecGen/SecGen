class Network
  attr_accessor :name, :range

  # Initialise object
  # @param [String] name network name
  # @param [String] range network range
  def initialize(name="", range="")
    @name = name
    @range = range
  end

  # Returns a string containing all object variables concatenated together
  # @return [String] hash contains all object variables
  def id
    hash = @name + @range
    return hash
    # return string that connects everything to 1 massive string
  end

  # Check if name matches networks.xml from scenario.xml
  # @param other ??????????
  def eql? other
    # checks if name matches networks.xml from scenario.xml
    other.kind_of?(self.class) && @name == other.name
  end

  # Returns a hash of the type
  # @return [Hash] hash of the type ????????
  def hash
    @type.hash
  end

end
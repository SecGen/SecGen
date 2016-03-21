class Service
  attr_accessor :name, :type, :details, :puppets

  # Initialise object
  # @param [String] name service name
  # @param [String] type service range
  # @param [String] details service details
  # @param [Array] puppets ??????????????
  def initialize(name="", type="", details="", puppets=[])
    @name = name
    @type = type
    @details = details
    @puppets = puppets
  end

  # Check if name matches services.xml from scenario.xml
  # @param other ??????????
  def eql? other
    other.kind_of?(self.class) && @type == other.type
  end

  # Returns a hash of the type
  # @return [Hash] hash of the type ????????
  def hash
    @type.hash
  end

  # Returns string containing the object type variable
  # @return [String] type contains services id string containing type value
  def id
    return @type
  end
end
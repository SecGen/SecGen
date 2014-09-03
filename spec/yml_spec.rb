class BoxNetwork
  attr_reader :range, :mask
end

class BoxDistro
  attr_reader :url
end

class BoxVuln
  
end

class YmlReader

  def load
    #return hashed/dictionary table 
  end

end

class Box
  attr_reader :networks, :distro, :vulns
end

class BoxSet
  attr_reader :boxes
end

class VagrantBuilder
  def build(boxset,dest)
  end
end

class YmlWriter
end

describe "test yml reader" do
  before :each do 
    ymlReader = YmlReader.new("mocks/networks.yml")
  end

  context "testing network yml"
  describe "read from network yml file" do
    it "reads ymlData returns hashed table" do
      begin
        ymlData = ymlReader.load
      rescue IOError
        # it failed!
      end
      #make sure yml data is a dictionary 

      #make an exact check to see if the hash table is correct
    end
    it "ymlData is a valid network object" do
    end
  end

  describe "write a network object to yml" do

    it "give yml writer the network object" do 
    end

    it "it matches the yml that the yml reader uses" do 
    end

  end
end
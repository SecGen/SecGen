describe "Yaml IO" do

  describe ".read" do
    it "raises an error when given nil"
    it "raises an error when file doesn't exist"
    it "converts a yaml list into an array"
    it "converts a yaml dictionary into a dictionary"
  end

  describe ".write" do
    it "raises an error when given nil"
    it "raises an error when given a string"
    it "converts a list to a yaml file"
    it "converts a dictionary to a yaml file"
  end

end

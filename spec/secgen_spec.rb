require 'secgen'

describe Secgen do

  describe ".configure" do
    context "when no block supplied" do
      it "returns the config singleton" do
        pending
        Secgen.configure.should eq(Secgen::Config)
      end
    end

    context "when a block is supplied" do
      # before do
      #   Secgen.configure { |config| config.default_distro = "precise64" }
      # end

      # after do
      #   Secgen.configure { |config| config.default_distro = "precise32" }
      # end

      it "sets the values on the config instance" do
        pending
        Secgen.default_distro.should eq "precise64"
      end
    end
  end

end

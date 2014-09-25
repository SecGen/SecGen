describe "Template" do

  describe ".from_file" do
    context "when fed nothing" do
      it "raises an exception"
    end

    context "when fed an argument" do
      it "should take a file path"
      it "should take a File object"
    end
  end

  describe "#new" do
    context "when fed nothing" do
      it "raises an exception"
    end

    context "when fed a string" do
      it "should take a string"
    end
  end

  describe "#dump" do
    subject { Secgen::Template.new("Hello {% world %}") }

    it "should eq 'Hello {% world %}'"
    it "should still eq 'Hello {% world %}' (no side-effects)"
  end

  describe "#render" do
    let(:template) do
      Secgen::Template.new("Something is rotten in the state of {% where %}")
    end

    it "removes markup" do
      pending
      template.render.should eq "Something is rotten in the state of "
    end

    it "injects the supplied arguments into the template" do
      pending
      template.set(:where => "Denmark")
      template.render.should eq "Something is rotten in the state of Denmark"
    end

    it "should not have any side effects" do
      pending
      template.dump.should eq "Something is rotten in the state of {% where %}"
    end
  end

end

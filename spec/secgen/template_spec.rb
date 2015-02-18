describe "Template" do
  describe ".from_file" do
    context "when fed nothing" do
      it "raises an exception"
    end

    context "when file doesn't exist" do
      it "should throw an exception"
    end

    context "when file does exist" do
      it "should accept a file path"
      it "should accept a File object"
      it "should throw an exception on an unreadable file"
    end
  end

  describe "#new" do
    it "should accept no parameters"
    it "should accept a template string"

    context "when given a file path" do
      it "should raise an exception if file doesn't exist"
      it "should raise an exception if file isn't readable"
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

describe "TemplateManager" do
  describe "#register_glob" do
    it "should store the glob"
  end

  describe "#process" do
    it "should throw an exception if glob root doesn't exist"

    context "when given a valid glob" do
      it "should make a Template for every template file"
      it "should return an empty list if no files match"
    end
  end
end

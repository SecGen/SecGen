$:.unshift File.expand_path('../lib', __FILE__)

describe "Template" do
  describe ".from_file" do
    subject { Secgen::Template.from_file(filepath) }

    context "when fed nothing" do
      let(:filepath) { nil }
      it { is_expected.to raise_error }
    end

    context "when file doesn't exist" do
      let(:filepath) { "non/existant/path" }
      it { is_expected.to raise_error(Secgen::TemplateError) }
    end

    context "when file exists" do
      let(:filepath) { "files/template.tpl" }
      it { is_expected.not_to raise_error(Secgen::TemplateError) }
    end

    context "when file is unreadable" do
      let(:filepath) { "files/unreadable_template.tpl" }
      it { is_expected.to raise_error(Secgen::TemplateError) }
    end
  end

  describe "#new" do
    subject { Secgen::Template.new(template) }

    context "given no parameters" do
      it { is_expected.not_to raise_error }
      its(:render) { is_expected.to be_empty }
      its(:dump)   { is_expected.to be_empty }
    end

    context "given a template string" do
      let(:template) { "Hello {% world %}" }
      it { is_expected.not_to raise_error }
      its(:render) { is_expected.to eq "Hello " }
      its(:dump)   { is_expected.to eq "Hello {% world %}" }
    end
  end

  describe "#dump" do
    subject { Secgen::Template.new("Hello {% world %}") }

    its(:dump) { is_expected.to eq "Hello {% world %}" }
    its(:dump) { is_expected.to eq "Hello {% world %}" } # no side-effects
  end

  describe "#render" do
    subject { Secgen::Template.new(template) }

    let(:template) { "Something is rotten in the state of {% where %}" }

    it "replaces variables in template" do
      template.set(:where => "Denmark")
      expect(subject.render).to eq "Something is rotten in the state of Denmark"
    end
  end
end

describe "TemplateManager" do
  subject do
    manager = Secgen::TemplateManager.new
    manager.register_glob(file_glob)
    manager
  end

  describe "#new" do
    context "when glob is invalid" do
      let(:file_glob) { "does/not/exist/*.tpl" }
      it { is_expected.to raise_error }
    end
  end

  describe "#list" do
    its(:list)  { is_expected.to be_empty }

    context "when it has matches" do
      let(:file_glob) { "spec/files/*.tpl" }

      its(:list)  { is_expected.not_to be_empty }
      its(:list)  { is_expected.to all be_a Secgen::Template }
      its(:list)  { is_expected.to all exist } # Requires Template::exists?
    end

    context "when it has no matches" do
      let(:file_glob) { "spec/files/nothing-matches-this-*.tpl" }
      its(:list)  { is_expected.to be_empty }
    end
  end

  describe "#files" do
    its(:files) { is_expected.to be_empty }

    context "when it has matches" do
      let(:file_glob) { "spec/files/*.tpl" }
      its(:files) { is_expected.not_to be_empty }
      its(:files) { is_expected.to all be_a String }
    end

    context "when it has no matches" do
      let(:file_glob) { "spec/files/nothing-matches-this-*.tpl" }
      its(:files) { is_expected.to be_empty }
    end
  end
end

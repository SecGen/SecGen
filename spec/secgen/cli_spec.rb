describe "CLI" do
  describe "#new" do
    it "generates ./Nodefile"
    it "interpolates variables in ./Nodefile"
    it "fails gracefully if CWD is already a node (or issue)"
  end

  describe "#info" do
    context "when CWD is a node" do
      it "reports node information"
    end

    context "when CWD is not a node" do
      it "fails gracefully"
    end
  end

  describe "#group" do
    context "when destination is invalid" do
      it "fails gracefully"
    end

    context "when specified nodes are invalid" do
      it "fails gracefully"
    end

    context "when specified nodes are valid" do
      it "fails gracefully"
      it "creates $dest/Nodesetfile"
      it "creates $dest/$node/ folder for each Node"
    end
  end

  describe "#update" do
    it "accepts no args (using CWD)"
    it "accepts node path"
    it "accepts node name"

    context "when specified node is invalid" do
      it "fails gracefully"
    end

    context "when specified node is valid" do
      it "checks for updates"

      context "updates are available" do
        it "pulls from issue's repo"
      end

      context "updates are not available" do
        it "fails gracefully"
      end
    end
  end

  describe "#deploy" do
    context "when 404 or 500" do
      it "fails gracefully"
    end

    context "it fails to build" do
      it "fails gracefully"
      it "cleans up after itself"
    end
  end

  describe "#issue" do
    describe "#new" do
      it "generates ./Issuefile"
      it "interpolates variables in ./Issuefile"
      it "fails gracefully if CWD is already a issue (or node)"
    end

    describe "#verify" do
      it "detects ruby syntax errors"
      it "detects invalid dependencies"
      it "detects template errors"
    end

    describe "#search" do
      context "on 401, 404 or 500" do
        it "fails gracefully"
      end

      it "prints a issue-name => url list to stdout"
    end
  end
end

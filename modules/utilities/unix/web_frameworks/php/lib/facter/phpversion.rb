Facter.add(:phpversion) do
  setcode do
    output = Facter::Util::Resolution.exec('php -v')

    unless output.nil?
      output.split("\n").first.split(' ').
        select { |x| x =~ %r{^(?:(\d+)\.)(?:(\d+)\.)?(\*|\d+)} }.first
    end
  end
end

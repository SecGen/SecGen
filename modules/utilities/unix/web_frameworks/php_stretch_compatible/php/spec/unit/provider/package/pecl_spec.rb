require 'spec_helper'

describe Puppet::Type.type(:package).provider(:pecl) do
  let(:resource) do
    Puppet::Type.type(:package).new name: 'dummy', ensure: :installed
  end

  let(:provider) do
    described_class.new(resource)
  end

  let(:parent_class) do
    Puppet::Type::Package::ProviderPear
  end

  before do
    parent_class.stubs(:command).with(:pear).returns '/fake/pear'
  end

  describe '.instances' do
    it 'returns pecl installed packages via pear' do
      parent_class.expects(:pear).
        with('list', '-a').
        returns File.read(fixtures('unit/provider/package/pear/list_a'))

      expect(described_class.instances.map(&:properties)).to eq [
        { ensure: '1.13.5', name: 'zip', vendor: 'pecl.php.net', provider: :pecl }
      ]
    end
  end

  describe '#install' do
    it 'installs with pear' do
      parent_class.expects(:pear)
      provider.install
    end
  end

  describe '#query' do
    it 'queries pecl package info via pear' do
      parent_class.expects(:pear).
        with('list', '-a').
        returns File.read(fixtures('unit/provider/package/pear/list_a'))

      resource[:name] = 'zip'
      expect(provider.query).to eq(ensure: '1.13.5', name: 'zip', vendor: 'pecl.php.net', provider: :pecl)
    end
  end

  describe '#latest' do
    it 'fetches the latest version available via pear' do
      parent_class.expects(:pear).
        with('remote-info', 'pecl.php.net/zip').
        returns File.read(fixtures('unit/provider/package/pear/remote-info_zip'))

      resource[:name] = 'zip'
      expect(provider.latest).to eq '1.13.5'
    end
  end

  describe '#uninstall' do
    it 'uninstalls a package via pear' do
      parent_class.expects(:pear).
        returns('uninstall ok')
      provider.uninstall
    end
  end

  describe '#update' do
    it 'updates to latest version via pear' do
      resource[:ensure] = '2.0'

      parent_class.expects(:pear)
      provider.update
    end
  end
end

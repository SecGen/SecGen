require 'spec_helper'

describe 'samba::server::install', :type => :class do
  context "on a Debian OS" do
    let(:facts) {{ :osfamily => 'Debian' }}
    it { should contain_package('samba') }
  end
end


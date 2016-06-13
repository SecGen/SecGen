require 'spec_helper'

describe 'samba::server::service' do
  context 'on a Debian os family' do
    let(:facts) {{ :osfamily => 'Debian' }}

    it { should contain_service('samba').with_require('Class[Samba::Server::Config]') }

    context 'Debian' do
      context 'wheezy' do
        let(:facts) {{ :osfamily => 'Debian',
                       :operatingsystem => 'Debian',
                       :operatingsystemmajrelease => '7' }}
        it { should contain_service('samba') }
      end
      context 'jessie' do
        let(:facts) {{ :osfamily => 'Debian',
                       :operatingsystem => 'Debian',
                       :operatingsystemmajrelease => '8' }}
        it { should contain_service('smbd') }
      end
    end

    context 'Ubuntu' do
      let(:facts) {{ :osfamily => 'Debian', :operatingsystem => 'Ubuntu' }}
      it { should contain_service('smbd') }
    end
  end

  context 'on a Redhat os family' do
    let(:facts) {{ :osfamily => 'Redhat' }}
    it { should contain_service('smb') }
  end

  context 'on a Archlinux os family' do
    let(:facts) {{ :osfamily => 'Archlinux' }}
    it { should contain_service('smbd') }
  end

  context 'on Linux os family' do
    let(:facts) {{ :osfamily => 'Linux' }}
    it { should raise_error(/is not supported by this module./) }

    context 'Gentoo' do
      let(:facts) {{ :osfamily => 'Linux', :operatingsystem => 'Gentoo' }}
      it { should contain_service('samba') }
    end
  end

  context 'on an unsupported OS' do
    let(:facts) {{ :osfamily => 'Solaris' }}
    it { should raise_error(/Solaris is not supported by this module./) }
  end
end

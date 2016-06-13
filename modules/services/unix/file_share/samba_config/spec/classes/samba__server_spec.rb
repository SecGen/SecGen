require 'spec_helper'

describe 'samba::server' do
  let(:facts) {{ :osfamily => 'Debian' }}

  it { should contain_class('samba::server::install') }
  it { should contain_class('samba::server::config') }
  it { should contain_class('samba::server::service') }

  it { should contain_samba__server__option('interfaces') }
  it { should contain_samba__server__option('bind interfaces only') }
  it { should contain_samba__server__option('security') }
  it { should contain_samba__server__option('server string') }
  it { should contain_samba__server__option('unix password sync') }
  it { should contain_samba__server__option('workgroup') }
  it { should contain_samba__server__option('socket options') }
  it { should contain_samba__server__option('deadtime') }
  it { should contain_samba__server__option('keepalive') }
  it { should contain_samba__server__option('load printers') }
  it { should contain_samba__server__option('printing') }
  it { should contain_samba__server__option('printcap name') }
  it { should contain_samba__server__option('disable spoolss') }

  it { should contain_file('/sbin/check_samba_user').with_owner('root') }
  it { should contain_file('/sbin/add_samba_user').with_owner('root') }
end

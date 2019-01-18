require 'spec_helper'

describe 'apt::source' do
  GPG_KEY_ID = '6F6B15509CF8E59E6E469F327F438280EF8D349F'.freeze

  let :pre_condition do
    'class { "apt": }'
  end

  let :title do
    'my_source'
  end

  context 'with defaults' do
    context 'without location' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          osfamily: 'Debian',
          lsbdistcodename: 'jessie',
          puppetversion: Puppet.version,
        }
      end

      it do
        is_expected.to raise_error(Puppet::Error, %r{source entry without specifying a location})
      end
    end
    context 'with location' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'jessie',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there' } }

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').without_content(%r{# my_source\ndeb-src hello.there wheezy main\n})
        is_expected.not_to contain_package('apt-transport-https')
      }
    end
  end

  describe 'no defaults' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        operatingsystem: 'Debian',
        lsbdistrelease: '8.0',
        puppetversion: Puppet.version,
      }
    end

    context 'with complex pin' do
      let :params do
        {
          location: 'hello.there',
          pin: { 'release' => 'wishwash',
                 'explanation' => 'wishwash',
                 'priority'    => 1001 },
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{hello.there jessie main\n})
      }

      it { is_expected.to contain_file('/etc/apt/sources.list.d/my_source.list').that_notifies('Class[Apt::Update]') }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: 1001,
                                                                                                            explanation: 'wishwash',
                                                                                                            release: 'wishwash')
      }
    end

    context 'with simple key' do
      let :params do
        {
          comment: 'foo',
          location: 'http://debian.mirror.iweb.ca/debian/',
          release: 'sid',
          repos: 'testing',
          key: GPG_KEY_ID,
          pin: '10',
          architecture: 'x86_64',
          allow_unsigned: true,
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# foo\ndeb \[arch=x86_64 trusted=yes\] http://debian.mirror.iweb.ca/debian/ sid testing\n})
                                                             .without_content(%r{deb-src})
      }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: '10',
                                                                                                            origin: 'debian.mirror.iweb.ca')
      }

      it {
        is_expected.to contain_apt__key("Add key: #{GPG_KEY_ID} from Apt::Source my_source").that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                                                                    id: GPG_KEY_ID)
      }
    end

    context 'with complex key' do
      let :params do
        {
          comment: 'foo',
          location: 'http://debian.mirror.iweb.ca/debian/',
          release: 'sid',
          repos: 'testing',
          key: { 'ensure' => 'refreshed',
                 'id' => GPG_KEY_ID,
                 'server' => 'pgp.mit.edu',
                 'content' => 'GPG key content',
                 'source'  => 'http://apt.puppetlabs.com/pubkey.gpg' },
          pin: '10',
          architecture: 'x86_64',
          allow_unsigned: true,
        }
      end

      it {
        is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# foo\ndeb \[arch=x86_64 trusted=yes\] http://debian.mirror.iweb.ca/debian/ sid testing\n})
                                                             .without_content(%r{deb-src})
      }

      it {
        is_expected.to contain_apt__pin('my_source').that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'present',
                                                                                                            priority: '10',
                                                                                                            origin: 'debian.mirror.iweb.ca')
      }

      it {
        is_expected.to contain_apt__key("Add key: #{GPG_KEY_ID} from Apt::Source my_source").that_comes_before('Apt::Setting[list-my_source]').with(ensure: 'refreshed',
                                                                                                                                                    id: GPG_KEY_ID,
                                                                                                                                                    server: 'pgp.mit.edu',
                                                                                                                                                    content: 'GPG key content',
                                                                                                                                                    source: 'http://apt.puppetlabs.com/pubkey.gpg')
      }
    end
  end

  context 'with allow_unsigned true' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        allow_unsigned: true,
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb \[trusted=yes\] hello.there jessie main\n})
    }
  end

  context 'with a https location, install apt-transport-https' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'HTTPS://foo.bar',
        allow_unsigned: false,
      }
    end

    it {
      is_expected.to contain_package('apt-transport-https')
    }
  end

  context 'with a https location, do not install apt-transport-https on oses not in list eg buster' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '10', full: '10.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'buster',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'https://foo.bar',
        allow_unsigned: false,
      }
    end

    it {
      is_expected.not_to contain_package('apt-transport-https')
    }
  end

  context 'with architecture equals x86_64' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '7', full: '7.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'wheezy',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'deb' => false, 'src' => true },
        architecture: 'x86_64',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb-src \[arch=x86_64\] hello.there wheezy main\n})
    }
  end

  context 'with architecture fact and unset architecture parameter' do
    let :facts do
      {
        architecture: 'amd64',
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'deb' => false, 'src' => true },
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb-src hello.there jessie main\n})
    }
  end

  context 'with include_src => true' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        location: 'hello.there',
        include: { 'src' => true },
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{# my_source\ndeb hello.there jessie main\ndeb-src hello.there jessie main\n})
    }
  end

  context 'with include deb => false' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'debian',
        lsbdistcodename: 'jessie',
        osfamily: 'debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        include: { 'deb' => false },
        location: 'hello.there',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').without_content(%r{deb-src hello.there wheezy main\n})
    }
    it { is_expected.to contain_apt__setting('list-my_source').without_content(%r{deb hello.there wheezy main\n}) }
  end

  context 'with include src => true and include deb => false' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'debian',
        lsbdistcodename: 'jessie',
        osfamily: 'debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        include: { 'deb' => false, 'src' => true },
        location: 'hello.there',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'present').with_content(%r{deb-src hello.there jessie main\n})
    }
    it { is_expected.to contain_apt__setting('list-my_source').without_content(%r{deb hello.there jessie main\n}) }
  end

  context 'with ensure => absent' do
    let :facts do
      {
        os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
        lsbdistid: 'Debian',
        lsbdistcodename: 'jessie',
        osfamily: 'Debian',
        puppetversion: Puppet.version,
      }
    end
    let :params do
      {
        ensure: 'absent',
      }
    end

    it {
      is_expected.to contain_apt__setting('list-my_source').with(ensure: 'absent')
    }
  end

  describe 'validation' do
    context 'with no release' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there' } }

      it do
        is_expected.to raise_error(Puppet::Error, %r{lsbdistcodename fact not available: release parameter required})
      end
    end

    context 'with release is empty string' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let(:params) { { location: 'hello.there', release: '' } }

      it { is_expected.to contain_apt__setting('list-my_source').with_content(%r{hello\.there  main}) }
    end

    context 'with invalid pin' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'jessie',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          pin: true,
        }
      end

      it do
        is_expected.to raise_error(Puppet::Error, %r{expects a value})
      end
    end

    context 'with notify_update = undef (default)' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'jessie',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(true) }
    end

    context 'with notify_update = true' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'jessie',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          notify_update: true,
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(true) }
    end

    context 'with notify_update = false' do
      let :facts do
        {
          os: { family: 'Debian', name: 'Debian', release: { major: '8', full: '8.0' } },
          lsbdistid: 'Debian',
          lsbdistcodename: 'jessie',
          osfamily: 'Debian',
          puppetversion: Puppet.version,
        }
      end
      let :params do
        {
          location: 'hello.there',
          notify_update: false,
        }
      end

      it { is_expected.to contain_apt__setting("list-#{title}").with_notify_update(false) }
    end
  end
end

require 'spec_helper_acceptance'

everything_everything_pp = <<-MANIFEST
      $sources = {
        'puppetlabs' => {
          'ensure'   => present,
          'location' => 'http://apt.puppetlabs.com',
          'repos'    => 'main',
          'key'      => {
            'id'     => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
            'server' => 'pool.sks-keyservers.net',
          },
        },
      }
      class { 'apt':
        update => {
          'frequency' => 'always',
          'timeout'   => 400,
          'tries'     => 3,
        },
        purge => {
          'sources.list'   => true,
          'sources.list.d' => true,
          'preferences'    => true,
          'preferences.d'  => true,
        },
        sources => $sources,
      }
  MANIFEST

describe 'apt class' do
  context 'with reset' do
    it 'fixes the sources.list' do
      shell('cp /etc/apt/sources.list /tmp')
    end
  end

  context 'with all the things' do
    it 'works with no errors' do
      # Apply the manifest (Retry if timeout error is received from key pool)
      retry_on_error_matching do
        apply_manifest(everything_everything_pp, catch_failures: true)
      end
    end
    it 'stills work' do
      shell('apt-get update')
      shell('apt-get -y --force-yes upgrade')
    end
  end

  context 'with reset' do
    it 'fixes the sources.list' do
      shell('cp /tmp/sources.list /etc/apt')
    end
  end
end

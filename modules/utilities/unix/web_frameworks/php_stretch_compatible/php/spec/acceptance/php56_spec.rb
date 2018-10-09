require 'spec_helper_acceptance'

describe 'with specific php version' do
  case default[:platform]
  when %r{ubuntu}
    packagename = 'php5.6-fpm'
  when %r{el}
    # ell = Enterprise Linux = CentOS....
    packagename = 'php-fpm'
  when %r{debian}
    packagename = 'php5-fpm'
  end

  context 'with params' do
    it 'works with 5.6' do
      pp = <<-EOS
      class { 'php::globals':
        php_version => '5.6',
      }
      -> class { 'php':
        ensure       => 'present',
        manage_repos => true,
        fpm          => true,
        dev          => true,
        composer     => true,
        pear         => true,
        phpunit      => false,
      }
      EOS

      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    describe package(packagename) do
      it { is_expected.to be_installed }
    end
    describe service(packagename) do
      it { is_expected.to be_running }
      it { is_expected.to be_enabled }
    end
    describe command('php --version') do
      its(:stdout) { is_expected.to match %r{5\.6} }
    end
  end
end

require 'spec_helper_acceptance'

describe 'php with default settings' do
  context 'default parameters' do
    it 'works with defaults' do
      pp = 'include php'
      # Run it twice and test for idempotency
      apply_manifest(pp, catch_failures: true)
      apply_manifest(pp, catch_changes: true)
    end

    case default[:platform]
    when %r{ubuntu-18.04}
      packagename = 'php7.2-fpm'
    when %r{ubuntu-16.04}
      packagename = 'php7.0-fpm'
    when %r{ubuntu-14.04}
      packagename = 'php5-fpm'
    when %r{el}
      packagename = 'php-fpm'
    when %r{debian-8}
      packagename = 'php5-fpm'
    when %r{debian-9}
      packagename = 'php7.0-fpm'
    end
    describe package(packagename) do
      it { is_expected.to be_installed }
    end

    describe service(packagename) do
      it { is_expected.to be_running }
      it { is_expected.to be_enabled }
    end
  end
  context 'default parameters with extensions' do
    case default[:platform]
    when %r{ubuntu-18.04}, %r{ubuntu-16.04}, %r{ubuntu-14.04}
      it 'works with defaults' do
        pp = <<-EOS
        class{'php':
          extensions => {
            'mysql'    => {},
            'gd'       => {},
            'net-url'  => {
              package_prefix => 'php-',
              settings       => {
                extension => undef
              }
            }
          }
        }
        EOS
        # Run it twice and test for idempotency
        apply_manifest(pp, catch_failures: true)
        apply_manifest(pp, catch_changes: true)
      end
    else
      it 'works with defaults' do
        pp = <<-EOS
        class{'php':
          extensions => {
            'mysql'    => {},
            'gd'       => {}
          }
        }
        EOS
        # Run it twice and test for idempotency
        apply_manifest(pp, catch_failures: true)
        apply_manifest(pp, catch_changes: true)
      end
    end

    case default[:platform]
    when %r{ubuntu-18.04}
      packagename = 'php7.2-fpm'
    when %r{ubuntu-16.04}
      packagename = 'php7.0-fpm'
    when %r{ubuntu-14.04}
      packagename = 'php5-fpm'
    when %r{el}
      packagename = 'php-fpm'
    when %r{debian-8}
      packagename = 'php5-fpm'
    when %r{debian-9}
      packagename = 'php7.0-fpm'
    end
    describe package(packagename) do
      it { is_expected.to be_installed }
    end

    describe service(packagename) do
      it { is_expected.to be_running }
      it { is_expected.to be_enabled }
    end
  end
end

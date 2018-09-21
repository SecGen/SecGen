require 'spec_helper_acceptance'

describe 'php' do
  it 'works with defaults' do
    pp = 'include php'
    # Run it twice and test for idempotency
    apply_manifest(pp, catch_failures: true)
    apply_manifest(pp, catch_changes: true)
  end

  case default[:platform]
  when %r{16.04}
    describe package('php7.0-fpm') do
      it { is_expected.to be_installed }
    end
  when %r{14.04}
    describe package('php5-fpm') do
      it { is_expected.to be_installed }
    end
  when %(7)
    describe package('php-fpm') do
      it { is_expected.to be_installed }
    end
  end
end

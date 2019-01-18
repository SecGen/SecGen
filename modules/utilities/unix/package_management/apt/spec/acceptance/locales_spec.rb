require 'spec_helper_acceptance'
require 'beaker/i18n_helper'

PUPPETLABS_GPG_KEY_LONG_ID     = '7F438280EF8D349F'.freeze
PUPPETLABS_LONG_FINGERPRINT    = '123456781274D2C8A956789A456789A456789A9A'.freeze

id_doesnt_match_fingerprint_pp = <<-MANIFEST
  apt_key { '#{PUPPETLABS_LONG_FINGERPRINT}':
    ensure  => 'present',
    content => '123456781274D2C8A956789A456789A456789A9B',
  }
MANIFEST

location_not_specified_fail_pp = <<-MANIFEST
  apt::source { 'puppetlabs':
    ensure  => 'present',
    repos   => 'main',
    key     => {
      id      => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
      server  => 'hkps.pool.sks-keyservers.net',
    },
  }
MANIFEST

invalid_title_pp = <<-MANIFEST
  apt::setting { 'test':
    ensure  => 'present',
    content => 'test'
  }
MANIFEST

no_content_param_pp = <<-MANIFEST
  apt::conf { 'test':
    ensure => 'present',
  }
MANIFEST

describe 'localization', if: (fact('osfamily') == 'Debian' || fact('osfamily') == 'RedHat') && (Gem::Version.new(puppet_version) >= Gem::Version.new('4.10.5')) do
  before :all do
    hosts.each do |host|
      on(host, "sed -i \"96i FastGettext.locale='ja'\" /opt/puppetlabs/puppet/lib/ruby/vendor_ruby/puppet.rb")
      change_locale_on(host, 'ja_JP.utf-8')
    end
  end

  describe 'ruby translations' do
    it 'translates an interpolated string' do
      apply_manifest(id_doesnt_match_fingerprint_pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{content/sourceが正当であるかを確認してください})
      end
    end
    it 'translates a simple string' do
      apply_manifest(location_not_specified_fail_pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{場所を指定せずにソースエントリを作成することはできません})
      end
    end
  end

  describe 'puppet translations' do
    it 'translates a concatenated string' do
      apply_manifest(invalid_title_pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{apt::settingのリソース名/タイトルの先頭は、'conf-'、'pref-'、'list-'にする必要があります})
      end
    end
    it 'translates a simple string' do
      apply_manifest(no_content_param_pp, expect_failures: true) do |r|
        expect(r.stderr).to match(%r{contentパラメータを渡す必要があります})
      end
    end
  end

  after :all do
    hosts.each do |host|
      on(host, 'sed -i "96d" /opt/puppetlabs/puppet/lib/ruby/vendor_ruby/puppet.rb')
      change_locale_on(host, 'en_US')
    end
  end
end

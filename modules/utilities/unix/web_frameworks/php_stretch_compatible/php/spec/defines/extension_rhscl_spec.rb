require 'spec_helper'

describe 'php::extension' do
  on_supported_os.each do |os, facts|
    next unless facts[:osfamily] == 'RedHat' || facts[:osfamily] == 'CentOS'

    context "on #{os}" do
      let :facts do
        facts
      end

      describe 'with rhscl_mode "remi" enabled: install one extension' do
        scl_php_version = 'php56'
        rhscl_mode = 'remi'
        configs_root = "/etc/opt/#{rhscl_mode}/#{scl_php_version}"

        let(:pre_condition) do
          "class {'::php::globals':
                    php_version => '#{scl_php_version}',
                    rhscl_mode => '#{rhscl_mode}'
          }->
          class {'::php':
                   ensure         => installed,
                   manage_repos   => false,
                   fpm            => false,
                   dev            => true, # must be true since we are using the provider => pecl (option installs header files)
                   composer       => false,
                   pear           => true,
                   phpunit        => false,
          }"
        end

        let(:title) { 'soap' }
        let(:params) do
          {
            ini_prefix: '20-',
            settings: {
              'bz2' => {
                'Date/date.timezone' => 'Europe/Berlin'
              }
            },
            multifile_settings: true
          }
        end

        it { is_expected.to contain_class('php::global') }
        it { is_expected.to contain_class('php') }
        it { is_expected.to contain_php__config('bz2').with(file: "#{configs_root}/php.d/20-bz2.ini") }
        it { is_expected.to contain_php__config__setting("#{configs_root}/php.d/20-bz2.ini: Date/date.timezone").with_value('Europe/Berlin') }
      end
    end
  end
end

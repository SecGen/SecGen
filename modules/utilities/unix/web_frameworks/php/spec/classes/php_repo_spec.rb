require 'spec_helper'

describe 'php::repo', type: :class do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let :facts do
        facts
      end

      let :pre_condition do
        'include php'
      end

      describe 'when configuring a package repo' do
        case facts[:osfamily]
        when 'Debian'
          case facts[:operatingsystem]
          when 'Debian'
            it { is_expected.to contain_class('php::repo::debian') }
          when 'Ubuntu'
            it { is_expected.to contain_class('php::repo::ubuntu') }
          end
        when 'Suse'
          it { is_expected.to contain_class('php::repo::suse') }
        when 'RedHat'
          it { is_expected.to contain_class('php::repo::redhat') }
        end
      end
    end
  end
end

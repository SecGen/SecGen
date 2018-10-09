require 'spec_helper'

describe 'php::config' do
  on_supported_os.each do |os, facts|
    context "on #{os}" do
      let :facts do
        facts
      end

      case facts[:operatingsystem]
      when 'Ubuntu'
        context 'default config' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php/5.6/conf.d/unique-name.ini',
              config: {}
            }
          end

          it { is_expected.to compile }
        end

        context 'simple example' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php/5.6/conf.d/unique-name.ini',
              config: {
                'apc.enabled' => 1
              }
            }
          end

          it { is_expected.to contain_php__config('unique-name').with_file('/etc/php/5.6/conf.d/unique-name.ini') }
        end

        context 'empty array' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php/5.6/conf.d/unique-name.ini',
              config: {}
            }
          end

          it { is_expected.to contain_php__config('unique-name').with_file('/etc/php/5.6/conf.d/unique-name.ini') }
        end

        context 'invalid config (string)' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php/5.6/conf.d/unique-name.ini',
              config: 'hello world'
            }
          end

          it { expect { is_expected.to raise_error(Puppet::Error) } }
        end
      else
        context 'default config' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php5/conf.d/unique-name.ini',
              config: {}
            }
          end

          it { is_expected.to compile }
        end

        context 'simple example' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php5/conf.d/unique-name.ini',
              config: {
                'apc.enabled' => 1
              }
            }
          end

          it { is_expected.to contain_php__config('unique-name').with_file('/etc/php5/conf.d/unique-name.ini') }
        end

        context 'empty array' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php5/conf.d/unique-name.ini',
              config: {}
            }
          end

          it { is_expected.to contain_php__config('unique-name').with_file('/etc/php5/conf.d/unique-name.ini') }
        end

        context 'invalid config (string)' do
          let(:title) { 'unique-name' }
          let(:params) do
            {
              file: '/etc/php5/conf.d/unique-name.ini',
              config: 'hello world'
            }
          end

          it { expect { is_expected.to raise_error(Puppet::Error) } }
        end
      end
    end
  end
end

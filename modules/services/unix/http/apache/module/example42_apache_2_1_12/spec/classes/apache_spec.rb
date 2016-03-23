require "#{File.join(File.dirname(__FILE__),'..','spec_helper.rb')}"

describe 'apache' do

  let(:title) { 'apache' }
  let(:node) { 'rspec.example42.com' }
  let(:facts) { { :ipaddress => '10.42.42.42' , :monitor_tool => 'puppi', :operatingsystemrelease => '6.6' } }

  describe 'Test standard installation' do
    it { should contain_package('apache').with_ensure('present') }
    it { should contain_service('apache').with_ensure('running') }
    it { should contain_service('apache').with_enable('true') }
    it { should contain_file('apache.conf').with_ensure('present') }
  end

  describe 'Test standard installation with monitoring and firewalling' do
    let(:params) { {:monitor => true , :firewall => true, :port => '42' } }

    it { should contain_package('apache').with_ensure('present') }
    it { should contain_service('apache').with_ensure('running') }
    it { should contain_service('apache').with_enable('true') }
    it { should contain_file('apache.conf').with_ensure('present') }
    it 'should monitor the process' do
      should contain_monitor__process('apache_process').with_enable(true)
    end
    it 'should place a firewall rule' do
      should contain_firewall('apache_tcp_42').with_enable(true)
    end
  end

  describe 'Test decommissioning - absent' do
    let(:params) { {:absent => true, :monitor => true , :firewall => true, :port => '42'} }

    it 'should remove Package[apache]' do should contain_package('apache').with_ensure('absent') end
    it 'should stop Service[apache]' do should contain_service('apache').with_ensure('stopped') end
    it 'should not enable at boot Service[apache]' do should contain_service('apache').with_enable('false') end
    it 'should remove apache configuration file' do should contain_file('apache.conf').with_ensure('absent') end
    it 'should not monitor the process' do
      should contain_monitor__process('apache_process').with_enable(false)
    end
    it 'should remove a firewall rule' do
      should contain_firewall('apache_tcp_42').with_enable(false)
    end
  end

  describe 'Test decommissioning - disable' do
    let(:params) { {:disable => true, :monitor => true , :firewall => true, :port => '42'} }

    it { should contain_package('apache').with_ensure('present') }
    it 'should stop Service[apache]' do should contain_service('apache').with_ensure('stopped') end
    it 'should not enable at boot Service[apache]' do should contain_service('apache').with_enable('false') end
    it { should contain_file('apache.conf').with_ensure('present') }
    it 'should not monitor the process' do
      should contain_monitor__process('apache_process').with_enable(false)
    end
    it 'should remove a firewall rule' do
      should contain_firewall('apache_tcp_42').with_enable(false)
    end
  end

  describe 'Test decommissioning - disableboot' do
    let(:params) { {:disableboot => true, :monitor => true , :firewall => true, :port => '42'} }

    it { should contain_package('apache').with_ensure('present') }
    it { should_not contain_service('apache').with_ensure('present') }
    it { should_not contain_service('apache').with_ensure('absent') }
    it 'should not enable at boot Service[apache]' do should contain_service('apache').with_enable('false') end
    it { should contain_file('apache.conf').with_ensure('present') }
    it 'should not monitor the process locally' do
      should contain_monitor__process('apache_process').with_enable(false)
    end
    it 'should keep a firewall rule' do
      should contain_firewall('apache_tcp_42').with_enable(true)
    end
  end

  describe 'Test customizations - template' do
    let(:params) { {:template => "apache/spec.erb" , :options => { 'opt_a' => 'value_a' } } }

    it 'should generate a valid template' do
      should contain_file('apache.conf').with_content(/fqdn: rspec.example42.com/)
    end
    it 'should generate a template that uses custom options' do
      should contain_file('apache.conf').with_content(/value_a/)
    end

  end

  describe 'Test customizations - source' do
    let(:params) { {:source => "puppet://modules/apache/spec" , :source_dir => "puppet://modules/apache/dir/spec" , :source_dir_purge => true } }

    it 'should request a valid source ' do
      should contain_file('apache.conf').with_source("puppet://modules/apache/spec")
    end
    it 'should request a valid source dir' do
      should contain_file('apache.dir').with_source("puppet://modules/apache/dir/spec")
    end
    it 'should purge source dir if source_dir_purge is true' do
      should contain_file('apache.dir').with_purge(true)
    end
  end

  describe 'Test customizations - custom class' do
    let(:params) { {:my_class => "apache::spec" } }
    it 'should automatically include a custom class' do
      should contain_file('apache.conf').with_content(/fqdn: rspec.example42.com/)
    end
  end

  describe 'Test service autorestart' do
    it 'should automatically restart the service, by default' do
      should contain_file('apache.conf').with_notify("Service[apache]")
    end
  end

  describe 'Test service autorestart' do
    let(:params) { {:service_autorestart => "no" } }

    it 'should not automatically restart the service, when service_autorestart => false' do
      should contain_file('apache.conf').with_notify(nil)
    end
  end

  describe 'Test Puppi Integration' do
    let(:params) { {:puppi => true, :puppi_helper => "myhelper"} }

    it 'should generate a puppi::ze define' do
      should contain_puppi__ze('apache').with_helper("myhelper")
    end
  end

  describe 'Test Monitoring Tools Integration' do
    let(:params) { {:monitor => true, :monitor_tool => "puppi" } }

    it 'should generate monitor defines' do
      should contain_monitor__process('apache_process').with_tool("puppi")
    end
  end

  describe 'Test Firewall Tools Integration' do
    let(:params) { {:firewall => true, :firewall_tool => "iptables" , :protocol => "tcp" , :port => "42" } }

    it 'should generate correct firewall define' do
      should contain_firewall('apache_tcp_42').with_tool("iptables")
    end
  end

  describe 'Test OldGen Module Set Integration' do
    let(:params) { {:monitor => "yes" , :monitor_tool => "puppi" , :firewall => "yes" , :firewall_tool => "iptables" , :puppi => "yes" , :port => "42" } }

    it 'should generate monitor resources' do
      should contain_monitor__process('apache_process').with_tool("puppi")
    end
    it 'should generate firewall resources' do
      should contain_firewall('apache_tcp_42').with_tool("iptables")
    end
    it 'should generate puppi resources ' do
      should contain_puppi__ze('apache').with_ensure("present")
    end
  end

  describe 'Test params lookup' do
    let(:facts) { { :monitor => true , :ipaddress => '10.42.42.42', :operatingsystemrelease => '6.6' } }
    let(:params) { { :port => '42' , :monitor_tool => 'puppi' } }

    it 'should honour top scope global vars' do
      should contain_monitor__process('apache_process').with_enable(true)
    end
  end

  describe 'Test params lookup' do
    let(:facts) { { :apache_monitor => true , :ipaddress => '10.42.42.42', :operatingsystemrelease => '6.6' } }
    let(:params) { { :port => '42' , :monitor_tool => 'puppi' } }

    it 'should honour module specific vars' do
      should contain_monitor__process('apache_process').with_enable(true)
    end
  end

  describe 'Test params lookup' do
    let(:facts) { { :monitor => false , :apache_monitor => true , :ipaddress => '10.42.42.42', :operatingsystemrelease => '6.6' } }
    let(:params) { { :port => '42' , :monitor_tool => 'puppi' } }

    it 'should honour top scope module specific over global vars' do
      should contain_monitor__process('apache_process').with_enable(true)
    end
  end

  describe 'Test params lookup' do
    let(:facts) { { :monitor => false , :ipaddress => '10.42.42.42', :operatingsystemrelease => '6.6' } }
    let(:params) { { :monitor => true , :monitor_tool => 'puppi' , :firewall => true, :port => '42' } }

    it 'should honour passed params over global vars' do
      should contain_monitor__process('apache_process').with_enable(true)
    end
  end

end


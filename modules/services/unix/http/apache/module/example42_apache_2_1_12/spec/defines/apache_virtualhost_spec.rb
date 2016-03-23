require "#{File.join(File.dirname(__FILE__),'..','spec_helper.rb')}"

describe 'apache::virtualhost' do

  let(:title) { 'apache::virtualhost' }
  let(:node) { 'rspec.example42.com' }
  let(:facts) { { :arch => 'i386' , :operatingsystem => 'redhat' } }
  let(:params) {
    { 'enable'       =>  'true',
      'name'         =>  'www.example42.com',
      'documentroot' =>  '/store/www',
    }
  }

  describe 'Test apache::virtualhost on redhat' do
    it 'should create a apache::virtualhost file' do
      should contain_file('ApacheVirtualHost_www.example42.com').with_ensure('present')
    end
    it 'should populate correctly the apache::virtualhost file DocumentRoot' do
      should contain_file('ApacheVirtualHost_www.example42.com').with_content(/    DocumentRoot \/store\/www/)
    end
    it 'should populate correctly the apache::virtualhost file ErrorLog' do
      should contain_file('ApacheVirtualHost_www.example42.com').with_content(/    ErrorLog  \/var\/log\/httpd\/www.example42.com-error_log/)
    end
    it 'should create the docroot directory' do
      should contain_file('/store/www').with_ensure("directory")
    end

  end

  describe 'Test apache::virtualhost on ubuntu' do
  let(:facts) { { :arch => 'i386' , :operatingsystem => 'ubuntu' } }
  let(:params) {
    { 'enable'       =>  'true',
      'name'         =>  'www.example42.com',
    }
  }

    it 'should create a apache::virtualhost link in sites-enabled' do
      should contain_file('ApacheVirtualHostEnabled_www.example42.com').with_ensure('/etc/apache2/sites-available/www.example42.com')
    end
    it 'should populate correctly the apache::virtualhost file DocumentRoot' do
      should contain_file('ApacheVirtualHost_www.example42.com').with_content(/    DocumentRoot \/var\/www\/www.example42.com/)
    end
    it 'should populate correctly the apache::virtualhost file ErrorLog' do
      should contain_file('ApacheVirtualHost_www.example42.com').with_content(/    ErrorLog  \/var\/log\/apache2\/www.example42.com-error_log/)
    end
    it 'should create the docroot directory' do
      should contain_file('/var/www/www.example42.com').with_ensure("directory")
    end

  end

  describe 'Test apache::virtualhost decommissioning' do
  let(:params) {
    { 'enable'       => 'false',
      'name'         => 'www.example42.com',
      'documentroot' => '/var/www/example42.com',
    }
  }

    it { should contain_file('ApacheVirtualHost_www.example42.com').with_ensure('absent') }
    it { should_not contain_file('/var/www/example42.com').with_ensure('directory') }
  end

end


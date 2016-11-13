class gitlist_040::configure {

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  # Create /home/git/repositories
  file { ['/home/git', '/home/git/repositories']:
    ensure => directory,
    owner  => 'www-data',
  }

  # Cloning the secgen repo as a repo with activity is required
  # if this adds too much time due to size we can replace with another repo later
  exec { 'clone-secgen-repo':
    cwd     => '/home/git/repositories/',
    command => 'git clone https://github.com/cliffe/secgen',
  }

  include ::apache
  include ::apache::mod::rewrite
  include ::apache::mod::php

  ::apache::vhost { 'www-gitlist':
    port            => '80',
    docroot         => '/var/www/gitlist',
    override        => 'All',
    redirect_source =>'/',
    redirect_dest   => '/gitlist/',
  }
}
class gitlist_040::configure {

  $secgen_parameters = parsejson($::json_inputs)
  $leaked_filename = $secgen_parameters['leaked_filename'][0]
  $github_repository = $secgen_parameters['github_repository'][0]

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  # Create /home/git/repositories
  file { ['/home/git', '/home/git/repositories']:
    ensure => directory,
    owner  => 'www-data',
  }

  # Cloning the secgen repo as a repo with activity is required
  # if this adds too much time due to size we can replace with another repo later
  exec { 'clone-github-repo':
    cwd     => '/home/git/repositories/',
    command => "git clone $github_repository",
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

  # Overshare, file leak
  file { "/var/www/gitlist/$leaked_filename":
    ensure  => present,
    owner   => 'www-data',
    mode    => '0750',
    content  => template('apache/overshare.erb')
  }
}
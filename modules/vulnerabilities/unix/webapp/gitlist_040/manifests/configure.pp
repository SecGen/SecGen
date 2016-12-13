class gitlist_040::configure {

  $secgen_parameters = parsejson($::json_inputs)
  $leaked_filenames = $secgen_parameters['leaked_filenames']
  $strings_to_leak = $secgen_parameters['strings_to_leak']
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

  ::secgen_functions::leak_files { 'gitlist_040-file-leak':
    storage_directory => '/var/www/gitlist/',
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => $strings_to_leak,
    owner             => 'www-data',
    mode              => '0750',
    leaked_from       => 'gitlist_040',
  }
}
class userspice_43::install {
  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $ip_address = $secgen_parameters['IP_address'][0]
  $dbname = $secgen_parameters['dbname'][0]
  $dbhost = $secgen_parameters['dbhost'][0]
  $dbuser = $secgen_parameters['dbuser'][0]
  $dbpass = $secgen_parameters['dbpass'][0]
  $docroot = '/var/www/userspice'
  $step2_params = "timezone=Europe%2FLondon&dbh=$dbhost&dbu=$dbuser&dbp=$dbpass&dbn=$dbname&copyright=&submit=Save+Settings+%3E%3E"

  # Copy install files to server
  file { $docroot:
    ensure  => directory,
    recurse => true,
    mode    => '0600',
    owner   => 'www-data',
    group   => 'www-data',
    source  => 'puppet:///modules/userspice_43/userspice',
  }

  ::mysql::db { $dbname:
    user     => $dbuser,
    password => $dbpass,
    host     => $dbhost,
    require  => File[$docroot],
    notify => File['/userspice.sh']
  }

  file { '/userspice.sh':
    owner   => 'root',
    group   => 'root',
    ensure  => present,
    mode    => '0755',
    content => template('userspice_43/userspice.sh.erb'),
    require => File[$docroot],
  }
}

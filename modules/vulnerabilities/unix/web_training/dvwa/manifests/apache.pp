class dvwa::apache {
  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $port = $secgen_parameters['port'][0]
  $db_password = $secgen_parameters['db_password'][0]
  $docroot = '/var/www/dvwa'

  if ($operatingsystem == 'Debian') {
    package { 'mysql-server':
      ensure => installed,
    }
    case $operatingsystemrelease {
      /^9.*/: { # do 9.x stretch stuff
        $php_version = "php7.0"
      }
      /^7.*/: { #do 7.x wheezy stuff
        $php_version = "php"
      }
    }
  } else {
    # kali
    $php_version = "php7.3"
  }

  package { ['php', 'php-mysqli', 'php-gd', 'libapache2-mod-php']:
    ensure => installed,
  }

  class { '::apache':
    default_vhost => false,
    default_mods => $php_version,
    overwrite_ports => false,
    mpm_module => 'prefork',
  }

  ::apache::vhost { 'dvwa':
    port    => $port,
    docroot => $docroot,

  }

  mysql::db { 'dvwa_database':
    user     => 'dvwa_user',
    password => $db_password,
    host     => 'localhost',
    grant    => ['SELECT', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP'],
  }

  mysql_user{ 'dvwa_user@localhost':
    ensure        => present,
    password_hash => mysql_password($db_password)
  }

  mysql_grant{'dvwa_user@localhost/dvwa_database.*':
    user       => 'dvwa_user@localhost',
    table      => 'dvwa_database.*',
    privileges => ['ALL'],
  }

}

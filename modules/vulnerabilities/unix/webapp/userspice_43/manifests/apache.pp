class userspice_43::apache {
  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $port = $secgen_parameters['port'][0]
  $docroot = '/var/www/userspice'

  package { ['php', 'phpmyadmin']:
    ensure => installed,
  }

  class { '::apache':
    default_vhost => false,
    default_mods => ['rewrite', 'php'],
    mpm_module => 'prefork'
  }

  ::apache::vhost { 'userspice':
    port    => $port,
    docroot => $docroot,
  }

}
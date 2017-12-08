class armitage::install{
  package { ['armitage']:
    ensure => 'installed',
  }

  # initialise the msf database, so that armitage can start it msfrpc on command
  exec { 'msfdb init':
    path    => ['/usr/bin', '/usr/sbin',],
  }
}

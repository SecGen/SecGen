class armitage::config{
  service { 'postgresql':
    enable => true,
    ensure => 'running',
  } ~>

  # initialise the msf database, so that armitage can start it msfrpc on command
  exec { 'msfdb reinit':
    path    => ['/usr/bin', '/usr/sbin',],
  }
}

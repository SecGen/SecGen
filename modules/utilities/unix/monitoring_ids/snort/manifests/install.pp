class snort::install {

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  file { '/usr/local/src/snort-2.9.12.tar.gz':
    source => "puppet:///modules/snort/snort-2.9.12.tar.gz",
    ensure => present,
  }

  file { '/usr/local/src/daq-2.0.6.tar.gz':
    source => "puppet:///modules/snort/daq-2.0.6.tar.gz",
    ensure => present,
  }

  exec { 'unpack-daq':
    cwd     => '/usr/local/src/',
    command => 'tar -xzvf daq-2.0.6.tar.gz',
    creates => '/usr/local/src/daq-2.0.6/',
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    requires => File['/usr/local/src/daq-2.0.6.tar.gz'],
  }

  exec { 'unpack-snort':
    cwd     => '/usr/local/src/',
    command => 'tar -xzvf snort-2.9.12.tar.gz',
    creates => '/usr/local/src/snort-2.9.12/',
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    requires => File['/usr/local/src/snort-2.9.12.tar.gz'],
  }



  # exec { 'install-snort':
  #   command => 'apt-get -y install snort || :',
  #   path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
  # }

  # package { ['snort']:
  #   ensure => 'installed',
  # }
}

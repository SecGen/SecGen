class snort::install {

  package { ['bison', 'flex', 'libdaq2','libdumbnet1','libpcap0.8','snort-common-libraries','libpcre3-dev', 'libdumbnet-dev']:
    ensure => installed,
  }

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  file { '/usr/local/src/libpcap-1.9.0.tar.gz':
    source => "puppet:///modules/snort/libpcap-1.9.0.tar.gz",
    ensure => present,
  }

  file { '/usr/local/src/daq-2.0.6.tar.gz':
    source => "puppet:///modules/snort/daq-2.0.6.tar.gz",
    ensure => present,
  }

  file { '/usr/local/src/snort-2.9.12.tar.gz':
    source => "puppet:///modules/snort/snort-2.9.12.tar.gz",
    ensure => present,
  }

  exec { 'unpack-libpcap':
    cwd     => '/usr/local/src/',
    command => 'tar -xzvf libpcap-1.9.0.tar.gz',
    creates => '/usr/local/src/libpcap-1.9.0/',
    require => File['/usr/local/src/libpcap-1.9.0.tar.gz'],
  }

  exec { 'unpack-daq':
    cwd     => '/usr/local/src/',
    command => 'tar -xzvf daq-2.0.6.tar.gz',
    creates => '/usr/local/src/daq-2.0.6/',
    require => File['/usr/local/src/daq-2.0.6.tar.gz'],
  }

  exec { 'unpack-snort':
    cwd     => '/usr/local/src/',
    command => 'tar -xzvf snort-2.9.12.tar.gz',
    creates => '/usr/local/src/snort-2.9.12/',
    require => File['/usr/local/src/snort-2.9.12.tar.gz'],
  }

  exec { 'install-libpcap':
    cwd => '/usr/local/src/libpcap-1.9.0/',
    command => '/usr/local/src/libpcap-1.9.0/configure --prefix=/usr && sudo make && sudo make install',
    require => Exec['unpack-libpcap']
  }

  exec { 'install-daq':
    cwd => '/usr/local/src/daq-2.0.6/',
    command => '/usr/local/src/daq-2.0.6/configure && sudo make && sudo make install',
    require => Exec['unpack-daq', 'install-libpcap']
  }

  exec { 'install-snort':
    cwd => '/usr/local/src/snort-2.9.12/',
    command => '/usr/local/src/snort-2.9.12/configure --enable-sourcefire --disable-open-appid && sudo make && sudo make install',
    require => Exec['unpack-snort', 'install-daq']
  }

  # Create a service file?
  file { 'install-service':
    ensure => file,
    content => template('snort/snort.service.erb'),
  }

  # package { ['snort']:
  #   ensure => 'installed',
  # }
}

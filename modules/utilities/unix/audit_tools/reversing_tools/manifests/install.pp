class reversing_tools::install {

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }
  ensure_packages(['gdb','git', 'ltrace', 'strace', 'pax-utils'])

  # Install Radare2

  file { '/opt/radare2-2.7.0.tar.gz':
    ensure => present,
    source => 'puppet:///modules/reversing_tools/radare2-2.7.0.tar.gz',
  }

  exec { 'unpack r2':
    cwd => '/opt/',
    command => 'tar -xzvf radare2-2.7.0.tar.gz',
  }

  exec { 'configure r2':
    cwd => '/opt/radare2-2.7.0/',
    command => '/bin/bash ./configure --prefix=/usr',
  }

  exec { 'make r2':
    cwd => '/opt/radare2-2.7.0/',
    command => '/usr/bin/make -j8',
  }

  exec { 'make install r2':
    cwd => '/opt/radare2-2.7.0/',
    command => 'make install',
  }

  # Install Detect It Easy from directory (TODO)

  # Install angr (TODO)

  # Install AFL?(TODO)
  # Install Driller?(TODO)

}
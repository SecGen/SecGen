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

  # Install Cutter (TODO)
  $cutter_dir = '/opt/Cutter'
  $cutter_appimage_url = 'https://github.com/radareorg/cutter/releases/download/v1.7.2/Cutter-v1.7.2-x86_64.Linux.AppImage'
  $cutter_filename = 'Cutter-v1.7.2-x86_64.Linux.AppImage'
  file { $cutter_dir:
    ensure => directory,
  }

  # Download image
  exec { 'download cutter appimage':
    command => "/usr/bin/wget -q $cutter_appimage_url -O $cutter_dir/$cutter_filename",
    cwd => $cutter_dir,
    require => File[$cutter_dir],
  }

  exec { 'chmod cutter':
    command => "/bin/chmod +x $cutter_dir/$cutter_filename",
    cwd => $cutter_dir,
    require => Exec['download cutter appimage'],
  }

  exec { 'install cutter':
    command => "/usr/bin/install $cutter_dir/$cutter_filename /usr/bin/cutter",
    cwd => $cutter_dir,
    require => Exec['download cutter appimage'],
  }

  # Install Detect It Easy from directory (TODO)

  # Install angr (TODO)

  # Install AFL?(TODO)
  # Install Driller?(TODO)

}
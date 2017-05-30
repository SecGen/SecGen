class dc16_amadhj::install {

  $base_directory = '/home/test'

  # Move Makefile and amadhj.c to box
  file { "$base_directory/Makefile":
    ensure => present,
    source => 'puppet:///modules/dc16_amadhj/Makefile',
    notify => File["$base_directory/amadhj.c"],
  }

  file { "$base_directory/amadhj.c":
    ensure => present,
    source => 'puppet:///modules/dc16_amadhj/amadhj.c',
    notify => Exec['gcc_amadhj_binary'],
  }

  # Build the binary with gcc
  exec { 'gcc_amadhj_binary':
    cwd => $base_directory,
    command => '/usr/bin/make'
  }

  # Drop the flag file on the box and set permissions  TODO: Replace with parameterised flag
  file { "$base_directory/flag":
    ensure => present,
    source => 'puppet:///modules/dc16_amadhj/flag',
    notify => Exec['gcc_amadhj_binary'],
  }

  # Remove Makefile and amadhj.c

}

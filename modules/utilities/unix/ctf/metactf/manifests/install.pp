class metactf::install {
  $install_dir = '/opt/metactf'

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  file { $install_dir:
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/metactf/repository',
  }

  exec { 'set install.sh mode':
    command => "chmod +x $install_dir/install.sh",
  }

  exec { 'install metactf dependencies':
    command => "/bin/bash $install_dir/install.sh"
  }

  # Determine how best to generate individual challenges at scenario level.

  # For now just build all of the binaries.

  # Modify the 'users file' to use accounts{} ? Do we even bother? It appears to only be used on the webapp anyway.
  # The filename is irrelevant.

  # Move the challenges based on account name.


}
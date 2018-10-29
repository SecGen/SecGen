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

}
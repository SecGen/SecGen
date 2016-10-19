class nfs_overshare::config {

  package { ['nfs-kernel-server', 'nfs-common', 'portmap']:
    ensure => installed
  }


  file { '/etc/exports':
    require => Package['nfs-common'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('nfs_overshare/exports.erb')
  }

  file { '/exports':
    require => Package['nfs-common'],
    ensure => 'directory',
    owner   => 'root',
    group   => 'root'
  }

  exec { "exportfs":
    require => Package['nfs-common'],
    command => "exportfs -a",
    path    => "/usr/sbin",
    # path    => [ "/usr/local/bin/", "/bin/" ],  # alternative syntax
  }

  file { '/exports/something':
    require => Package['nfs-common'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('nfs_overshare/overshare.erb')
  }

  # file { '/tmp/file02':
  #   ensure  => file,
  #   content => 'Yeah, I am file02, so what?',
  # }
  # strings_to_leak_location

}

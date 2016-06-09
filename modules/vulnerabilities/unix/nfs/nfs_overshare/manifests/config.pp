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

  exec { "exportfs":
      require => Package['nfs-common'],
      command => "exportfs -a",
      path    => "/usr/sbin",
      # path    => [ "/usr/local/bin/", "/bin/" ],  # alternative syntax
  }
}



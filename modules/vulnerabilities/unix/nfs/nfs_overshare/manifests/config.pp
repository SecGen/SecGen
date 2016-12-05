class nfs_overshare::config {

  # Setup SecGen Parameters
  $secgen_parameters=parsejson($::json_inputs)
  $leaked_filename=$secgen_parameters['leaked_filename'][0]
  $storage_directory=$secgen_parameters['storage_directory'][0]

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

  file { $storage_directory:
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

  file { "$storage_directory/$leaked_filename":
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

class nfs_share::config {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)
  $storage_directory = $secgen_parameters['storage_directory'][0]

  package { ['nfs-kernel-server', 'nfs-common', 'portmap']:
      ensure => installed
  }

  group { 'wheel':
    ensure => present,
  }

  file { $storage_directory:
    ensure => 'directory',
    owner  => 'root',
    group  => 'wheel',
    mode   => '0754',
  }
  
  file { '/etc/exports':
    require => Package['nfs-common'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('nfs_share/exports.erb')
  }

  exec { "exportfs":
      require => Package['nfs-common'],
      command => "exportfs -a",
      path    => "/usr/sbin",
      # path    => [ "/usr/local/bin/", "/bin/" ],  # alternative syntax
  }
}



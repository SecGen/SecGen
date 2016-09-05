class nfs_overshare::config {

  file { '/export_nfs/something':
    require => Package['nfs-common'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('nfs_overshare/overshare.erb')
  }

}

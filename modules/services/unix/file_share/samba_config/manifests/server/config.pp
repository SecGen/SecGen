# == Class samba::server::config
#
class samba::server::config {
  file { '/etc/samba':
    ensure => directory,
    owner  => 'root',
    group  => 'root',
    mode   => '0755',
  }

  file { '/etc/samba/smb.conf':
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0644',
    require => [File['/etc/samba'], Class['samba::server::install']],
    notify  => Class['samba::server::service']
  }
}

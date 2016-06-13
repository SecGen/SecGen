# == Class samba::server::server
#
class samba::server::service (
  $ensure = running,
  $enable = true
) inherits samba::server::params {

  service { $samba::server::params::service_name :
    ensure     => $ensure,
    hasstatus  => true,
    hasrestart => true,
    enable     => $enable,
    require    => Class['samba::server::config']
  }

  if $samba::server::params::nmbd_name != undef {
    service { $samba::server::params::nmbd_name :
      ensure     => $ensure,
      hasrestart => false,
      enable     => $enable,
      require    => Class['samba::server::config'],
    }
  }
}

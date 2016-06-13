# == Class samba::server::install
#
class samba::server::install {
  package { 'samba':
    ensure => installed
  }
}

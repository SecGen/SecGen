# == Class samba
#
class samba {
  include samba::server

  if samba::server::security == 'ads' {
    include samba::server::ads
  }
}

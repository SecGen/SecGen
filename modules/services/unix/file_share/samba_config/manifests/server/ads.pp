# == Class samba::server::ads
# This module join samba server to Active Dirctory
#
class samba::server::ads($ensure = present,
  $winbind_acct               = 'admin',
  $winbind_pass               = 'SecretPass',
  $realm                      = 'domain.com',
  $winbind_uid                = '10000-20000',
  $winbind_gid                = '10000-20000',
  $winbind_enum_groups        = 'yes',
  $winbind_enum_users         = 'yes',
  $winbind_use_default_domain = 'yes',
  $nsswitch                   = false,
  $acl_group_control          = 'yes',
  $map_acl_inherit            = 'yes',
  $inherit_acls               = 'yes',
  $store_dos_attributes       = 'yes',
  $ea_support                 = 'yes',
  $dos_filemode               = 'yes',
  $acl_check_permissions      = false,
  $map_system                 = 'no',
  $map_archive                = 'no',
  $map_readonly               = 'no',
  $target_ou                  = 'Nix_Mashine') {

  $krb5_user_package = $::osfamily ? {
    'RedHat' => 'krb5-workstation',
    default  => 'krb5-user',
  }

  if $::osfamily == 'RedHat' {
    if $::operatingsystemrelease =~ /^6\./ {
      $winbind_package = 'samba-winbind'
    } else {
      $winbind_package = 'samba-common'
    }
  } else {
    $winbind_package = 'winbind'
  }

  package{
    $krb5_user_package: ensure => installed;
    $winbind_package:   ensure => installed;
    'expect':           ensure => installed;
  }

  include samba::server::config
  include samba::server::winbind

  # notify winbind
  samba::server::option {
    'realm':                        value => $realm,
    notify                                => Class['Samba::Server::Winbind'];
    'winbind uid':                  value => $winbind_uid,
    notify                                => Class['Samba::Server::Winbind'];
    'winbind gid':                  value => $winbind_gid,
    notify                                => Class['Samba::Server::Winbind'];
    'winbind enum groups':          value => $winbind_enum_groups,
    notify                                => Class['Samba::Server::Winbind'];
    'winbind enum users':           value => $winbind_enum_users,
    notify                                => Class['Samba::Server::Winbind'];
    'winbind use default domain':   value => $winbind_use_default_domain,
    notify                                => Class['Samba::Server::Winbind'];
  }

  samba::server::option {
    'acl group control':            value => $acl_group_control;
    'map acl inherit':              value => $map_acl_inherit;
    'inherit acls':                 value => $inherit_acls;
    'store dos attributes':         value => $store_dos_attributes;
    'ea support':                   value => $ea_support;
    'dos filemode':                 value => $dos_filemode;
    'acl check permissions':        value => $acl_check_permissions;
    'map system':                   value => $map_system;
    'map archive':                  value => $map_archive;
    'map readonly':                 value => $map_readonly;
  }

  $nss_file = 'etc/nsswitch.conf'

  $changes = $nsswitch ? {
      true => [
        'set database[. = "passwd"]/service[1] compat',
        'set database[. = "passwd"]/service[2] winbind',
        'set database[. = "group"]/service[1] compat',
        'set database[. = "group"]/service[2] winbind',
      ],
      false => [
        "rm /files/${nss_file}/database[. = 'passwd']/service[. = 'winbind']",
        "rm /files/${nss_file}/database[. = 'group']/service[. = 'winbind']",
      ]
    }

  augeas { 'nsswitch':
    context => "/files/${nss_file}",
    changes => $changes
  }

  file {'verify_active_directory':
    # this script returns 0 if join is intact
    path    => '/sbin/verify_active_directory',
    owner   => root,
    group   => root,
    mode    => '0755',
    content => template("${module_name}/verify_active_directory.erb"),
    require => [ Package[$krb5_user_package, $winbind_package, 'expect'],
      Augeas['samba-realm', 'samba-security', 'samba-winbind enum users',
        'samba-winbind enum groups', 'samba-winbind uid', 'samba-winbind gid',
        'samba-winbind use default domain'], Service['winbind'] ],
  }

  file {'configure_active_directory':
    # this script joins or leaves a domain
    path    => '/sbin/configure_active_directory',
    owner   => root,
    group   => root,
    mode    => '0755',
    content => template("${module_name}/configure_active_directory.erb"),
    require => [ Package[$krb5_user_package, $winbind_package, 'expect'],
      Augeas['samba-realm', 'samba-security', 'samba-winbind enum users',
        'samba-winbind enum groups', 'samba-winbind uid', 'samba-winbind gid',
        'samba-winbind use default domain'], Service['winbind'] ],
  }

  exec {'join-active-directory':
    # join the domain configured in samba.conf
    command => '/sbin/configure_active_directory -j',
    unless  => '/sbin/verify_active_directory',
    require => [ File['configure_active_directory', 'verify_active_directory'], Service['winbind'] ],
  }
}

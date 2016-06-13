# == Class samba::server
#
class samba::server($interfaces = '',
                    $security = '',
                    $server_string = '',
                    $unix_password_sync = '',
                    $netbios_name = '',
                    $workgroup = '',
                    $socket_options = '',
                    $deadtime = '',
                    $keepalive = '',
                    $load_printers = '',
                    $printing = '',
                    $printcap_name = '',
                    $map_to_guest = '',
                    $disable_spoolss = '',
                    $kernel_oplocks = '',
                    $pam_password_change = '',
                    $os_level = '',
                    $preferred_master = '',
                    $bind_interfaces_only = 'yes',) {

  include samba::server::install
  include samba::server::config
  include samba::server::service

  $incl    = '/etc/samba/smb.conf'
  $context = '/files/etc/samba/smb.conf'
  $target  = 'target[. = "global"]'

  augeas { 'global-section':
    incl    => $incl,
    lens    => 'Samba.lns',
    context => $context,
    changes => "set ${target} global",
    require => Class['samba::server::config'],
    notify  => Class['samba::server::service']
  }

  samba::server::option {
    'interfaces':           value => $interfaces;
    'bind interfaces only': value => $bind_interfaces_only;
    'security':             value => $security;
    'server string':        value => $server_string;
    'unix password sync':   value => $unix_password_sync;
    'netbios name':         value => $netbios_name;
    'workgroup':            value => $workgroup;
    'socket options':       value => $socket_options;
    'deadtime':             value => $deadtime;
    'keepalive':            value => $keepalive;
    'load printers':        value => $load_printers;
    'printing':             value => $printing;
    'printcap name':        value => $printcap_name;
    'map to guest':         value => $map_to_guest;
    'disable spoolss':      value => $disable_spoolss;
    'kernel oplocks':       value => $kernel_oplocks;
    'pam password change':  value => $pam_password_change;
    'os level':             value => $os_level;
    'preferred master':     value => $preferred_master;
  }

  file {'/sbin/check_samba_user':
    # script checks to see if a samba account exists for a given user
    owner   => root,
    group   => root,
    mode    => '0755',
    content => template("${module_name}/check_samba_user"),
  }

  file {'/sbin/add_samba_user':
    # script creates a new samba account for a given user and password
    owner   => root,
    group   => root,
    mode    => '0755',
    content => template("${module_name}/add_samba_user"),
  }
}

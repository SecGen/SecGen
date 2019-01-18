# @summary Provides defaults for the Apt module parameters.
# 
# @api private
#
class apt::params {

  if $::osfamily != 'Debian' {
    fail(translate('This module only works on Debian or derivatives like Ubuntu'))
  }

  $root           = '/etc/apt'
  $provider       = '/usr/bin/apt-get'
  $sources_list   = "${root}/sources.list"
  $sources_list_d = "${root}/sources.list.d"
  $trusted_gpg_d  = "${root}/trusted.gpg.d"
  $conf_d         = "${root}/apt.conf.d"
  $preferences    = "${root}/preferences"
  $preferences_d  = "${root}/preferences.d"
  $keyserver      = 'keyserver.ubuntu.com'
  $confs          = {}
  $update         = {}
  $purge          = {}
  $proxy          = {}
  $sources        = {}
  $keys           = {}
  $ppas           = {}
  $pins           = {}
  $settings       = {}
  $auth_conf_entries = []

  $config_files = {
    'conf'   => {
      'path' => $conf_d,
      'ext'  => '',
    },
    'pref'   => {
      'path' => $preferences_d,
      'ext'  => '.pref',
    },
    'list'   => {
      'path' => $sources_list_d,
      'ext'  => '.list',
    }
  }

  $update_defaults = {
    'frequency' => 'reluctantly',
    'loglevel'  => undef,
    'timeout'   => undef,
    'tries'     => undef,
  }

  $proxy_defaults = {
    'ensure' => undef,
    'host'   => undef,
    'port'   => 8080,
    'https'  => false,
    'direct' => false,
  }

  $purge_defaults = {
    'sources.list'   => false,
    'sources.list.d' => false,
    'preferences'    => false,
    'preferences.d'  => false,
  }

  $source_key_defaults = {
    'server'  => $keyserver,
    'options' => undef,
    'content' => undef,
    'source'  => undef,
  }

  $include_defaults = {
    'deb' => true,
    'src' => false,
  }

  case $facts['os']['name']{
    'Debian': {
          $backports = {
            'location' => 'http://deb.debian.org/debian',
            'key'      => 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553',
            'repos'    => 'main contrib non-free',
          }
      $ppa_options = undef
      $ppa_package = undef
    }
    'Ubuntu': {
      $backports = {
        'location' => 'http://archive.ubuntu.com/ubuntu',
        'key'      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
        'repos'    => 'main universe multiverse restricted',
      }
      $ppa_options        = '-y'
      $ppa_package        = 'software-properties-common'
    }
    undef: {
      fail(translate('Unable to determine value for fact os[\"name\"]'))
    }
    default: {
      $ppa_options = undef
      $ppa_package = undef
      $backports   = undef
    }
  }
}

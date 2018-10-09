# Install and configure php CLI
#
# === Parameters
#
# [*inifile*]
#   The path to the ini php5-cli ini file
#
# [*settings*]
#   Hash with nested hash of key => value to set in inifile
#
class php::cli(
  Stdlib::Absolutepath $inifile = $php::params::cli_inifile,
  Hash $settings                = {}
) inherits php::params {

  assert_private()

  if $php::globals::rhscl_mode {
    # stupid fixes for scl
    file {'/usr/bin/pear':
      ensure => 'link',
      target => "${$php::params::php_bin_dir}/pear",
    }

    file {'/usr/bin/pecl':
      ensure => 'link',
      target => "${$php::params::php_bin_dir}/pecl",
    }

    file {'/usr/bin/php':
      ensure => 'link',
      target => "${$php::params::php_bin_dir}/php",
    }
  }

  $real_settings = deep_merge($settings, hiera_hash('php::cli::settings', {}))

  if $inifile != $php::params::config_root_inifile {
    # only create a cli specific inifile if the filenames are different
    ::php::config { 'cli':
      file   => $inifile,
      config => $real_settings,
    }
  }
}

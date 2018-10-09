# Install and configure mod_php for fpm
#
# === Parameters
#
# [*inifile*]
#   Absolute path to the global php.ini file. Defaults
#   to the OS specific default location as defined in params.
# [*settings*]
#   Hash of settings to apply to the global php.ini file.
#   Defaults to OS specific defaults (i.e. add nothing)
#

#
class php::global(
  Stdlib::Absolutepath $inifile = $php::config_root_inifile,
  Hash $settings                = {}
) inherits ::php {

  assert_private()

  # No deep merging required since the settings we have are the global settings.
  $real_settings = $settings

  ::php::config { 'global':
    file   => $inifile,
    config => $real_settings,
  }
}

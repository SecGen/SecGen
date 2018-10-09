# Install and configure php embedded SAPI
#
# === Parameters
#
# [*inifile*]
#   The path to the ini php5-embeded ini file
#
# [*settings*]
#   Hash with nested hash of key => value to set in inifile
#
# [*package*]
#   Specify which package to install
#
# [*ensure*]
#   Specify which version of the package to install
#
class php::embedded(
  String $ensure                = $php::ensure,
  String $package               = "${php::package_prefix}${php::params::embedded_package_suffix}",
  Stdlib::Absolutepath $inifile = $php::params::embedded_inifile,
  Hash $settings                = {},
) inherits php::params {

  assert_private()

  $real_settings = deep_merge(
    $settings,
    hiera_hash('php::embedded::settings', {})
  )

  $real_package = $facts['os']['family'] ? {
    'Debian' => "lib${package}",
    default   => $package,
  }

  package { $real_package:
    ensure  => $ensure,
    require => Class['php::packages'],
  }
  -> php::config { 'embedded':
    file   => $inifile,
    config => $real_settings,
  }

}

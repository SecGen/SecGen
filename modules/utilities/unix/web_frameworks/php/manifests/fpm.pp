# Install and configure mod_php for fpm
#
# === Parameters
#
# [*user*]
#   The user that php-fpm should run as
#
# [*group*]
#   The group that php-fpm should run as
#
# [*service_enable*]
#   Enable/disable FPM service
#
# [*service_ensure*]
#   Ensure FPM service is either 'running' or 'stopped'
#
# [*service_name*]
#   This is the name of the php-fpm service. It defaults to reasonable OS
#   defaults but can be different in case of using php7.0/other OS/custom fpm service
#
# [*service_provider*]
#   This is the name of the service provider, in case there is a non
#   OS default service provider used to start FPM.
#   Defaults to 'undef', pick system defaults.
#
# [*pools*]
#   Hash of php::fpm::pool resources that will be created. Defaults
#   to a single php::fpm::pool named www with default parameters.
#
# [*log_owner*]
#   The php-fpm log owner
#
# [*log_group*]
#   The group owning php-fpm logs
#
# [*package*]
#   Specify which package to install
#
# [*ensure*]
#   Specify which version of the package to install
#
# [*inifile*]
#   Path to php.ini for fpm
#
# [*settings*]
#   fpm settings hash
#
# [*global_pool_settings*]
#   Hash of defaults params php::fpm::pool resources that will be created.
#   Defaults is empty hash.
#
class php::fpm (
  String $ensure                = $::php::ensure,
  $user                         = $::php::fpm_user,
  $group                        = $::php::fpm_group,
  $service_ensure               = $::php::fpm_service_ensure,
  $service_enable               = $::php::fpm_service_enable,
  $service_name                 = $::php::fpm_service_name,
  $service_provider             = $::php::fpm_service_provider,
  String $package               = $::php::real_fpm_package,
  Stdlib::Absolutepath $inifile = $::php::fpm_inifile,
  Hash $settings                = $::php::real_settings,
  $global_pool_settings         = $::php::real_fpm_global_pool_settings,
  Hash $pools                   = $::php::real_fpm_pools,
  $log_owner                    = $::php::log_owner,
  $log_group                    = $::php::log_group,
) {

  if ! defined(Class['php']) {
    warning('php::fpm is private')
  }

  $real_settings = deep_merge($settings, hiera_hash('php::fpm::settings', {}))

  # On FreeBSD fpm is not a separate package, but included in the 'php' package.
  # Implies that the option SET+=FPM was set when building the port.
  $real_package = $facts['os']['family'] ? {
    'FreeBSD' => [],
    default   => $package,
  }

  package { $real_package:
    ensure  => $ensure,
    require => Class['::php::packages'],
  }

  class { '::php::fpm::config':
    user      => $user,
    group     => $group,
    inifile   => $inifile,
    settings  => $real_settings,
    log_owner => $log_owner,
    log_group => $log_group,
    require   => Package[$real_package],
  }
  contain '::php::fpm::config'
  contain '::php::fpm::service'

  Class['php::fpm::config'] ~> Class['php::fpm::service']

  $real_global_pool_settings = hiera_hash('php::fpm::global_pool_settings', $global_pool_settings)
  $real_pools = hiera_hash('php::fpm::pools', $pools)
  create_resources(::php::fpm::pool, $real_pools, $real_global_pool_settings)

  # Create an override to use a reload signal as trusty and utopic's
  # upstart version supports this
  if ($facts['os']['name'] == 'Ubuntu'
      and versioncmp($facts['os']['release']['full'], '14') >= 0
      and versioncmp($facts['os']['release']['full'], '16') < 0) {
    if ($service_enable) {
      $fpm_override = 'reload signal USR2'
    }
    else {
      $fpm_override = "reload signal USR2\nmanual"
    }
    file { "/etc/init/${::php::fpm::service::service_name}.override":
      content => $fpm_override,
      before  => Package[$real_package],
    }
  }
}

# Install the development package with headers for PHP
#
# === Parameters
#
# [*ensure*]
#   The PHP ensure of PHP dev to install
#
# [*package*]
#   The package name for the PHP development files
#
class php::dev(
  String $ensure        = $php::ensure,
  String $package       = "${php::package_prefix}${php::params::dev_package_suffix}",
  Boolean $manage_repos = $php::manage_repos,
) inherits php::params {

  assert_private()

  # On FreeBSD there is no 'devel' package.
  $real_package = $facts['os']['family'] ? {
    'FreeBSD' => [],
    default   => $package,
  }

  if $facts['os']['family'] == 'Debian' {
    # we can set the dependency only if we manage repos
    $require = $manage_repos ? {
      true  => Class['::apt::update'],
      false => undef,
    }
  } else {
    $require = undef
  }

  # Default PHP come with xml module and no seperate package for it
  if $facts['os']['name'] == 'Ubuntu' and versioncmp($facts['os']['release']['full'], '16.04') >= 0  {
    ensure_packages(["${php::package_prefix}xml"], {
      ensure  => present,
      require => $require,
    })
  }
  package { $real_package:
    ensure  => $ensure,
    require => Class['php::packages'],
  }
}

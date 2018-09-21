# Install a PHP extension package
#
# === Parameters
#
# [*ensure*]
#   The ensure of the package to install
#   Could be "latest", "installed" or a pinned version
#
# [*package_prefix*]
#   Prefix to prepend to the package name for the package provider
#
# [*provider*]
#   The provider used to install the package
#   Could be "pecl", "apt", "dpkg" or any other OS package provider
#   If set to "none", no package will be installed
#
# [*source*]
#   The source to install the extension from. Possible values
#   depend on the *provider* used
#
# [*header_packages*]
#   System packages dependencies to install for extensions (e.g. for
#   memcached libmemcached-dev on Debian)
#
# [*compiler_packages*]
#   System packages dependencies to install for compiling extensions
#   (e.g. build-essential on Debian)
#
# [*responsefile*]
#   File containing answers for interactive extension setup. Supported
#   *providers*: pear, pecl.
#
# [*install_options*]
#   Array of String or Hash options to pass to the provider.
#
define php::extension::install (
  String           $ensure                          = 'installed',
  Optional[Php::Provider] $provider                 = undef,
  Optional[String] $source                          = undef,
  String           $package_prefix                  = $::php::package_prefix,
  Optional[Stdlib::AbsolutePath] $responsefile      = undef,
  Variant[String, Array[String]] $header_packages   = [],
  Variant[String, Array[String]] $compiler_packages = $::php::params::compiler_packages,
  Php::InstallOptions $install_options              = undef,
) {

  if ! defined(Class['php']) {
    warning('php::extension::install is private')
  }

  case $provider {
    /pecl|pear/: {
      $real_package = $title

      unless empty($header_packages) {
        ensure_resource('package', $header_packages)
        Package[$header_packages] -> Package[$real_package]
      }
      unless empty($compiler_packages) {
        ensure_resource('package', $compiler_packages)
        Package[$compiler_packages] -> Package[$real_package]
      }

      $package_require      = [
        Class['::php::pear'],
        Class['::php::dev'],
      ]
    }

    'none' : {
      debug("No package installed for php::extension: `${title}`.")
    }

    default: {
      $real_package = "${package_prefix}${title}"
      $package_require = undef
    }
  }

  unless $provider == 'none' {
    package { $real_package:
      ensure          => $ensure,
      provider        => $provider,
      source          => $source,
      responsefile    => $responsefile,
      install_options => $install_options,
      require         => $package_require,
    }
  }
}

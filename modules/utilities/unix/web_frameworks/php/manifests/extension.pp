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
# [*so_name*]
#   The DSO name of the package (e.g. opcache for zendopcache)
#
# [*ini_prefix*]
#   An optional filename prefix for the settings file of the extension
#
# [*php_api_version*]
#   This parameter is used to build the full path to the extension
#   directory for zend_extension in PHP < 5.5 (e.g. 20100525)
#
# [*header_packages*]
#   System packages dependencies to install for extensions (e.g. for
#   memcached libmemcached-dev on Debian)
#
# [*compiler_packages*]
#   System packages dependencies to install for compiling extensions
#   (e.g. build-essential on Debian)
#
# [*zend*]
#  Boolean parameter, whether to load extension as zend_extension.
#  Defaults to false.
#
# [*settings*]
#   Nested hash of global config parameters for php.ini
#
# [*settings_prefix*]
#   Boolean/String parameter, whether to prefix all setting keys with
#   the extension name or specified name. Defaults to false.
#
# [*sapi*]
#   String parameter, whether to specify ALL sapi or a specific sapi.
#   Defaults to ALL.
#
# [*responsefile*]
#   File containing answers for interactive extension setup. Supported
#   *providers*: pear, pecl.
#
# [*install_options*]
#   Array of String or Hash options to pass to the provider.
#
define php::extension (
  String           $ensure                          = 'installed',
  Optional[Php::Provider] $provider                 = undef,
  Optional[String] $source                          = undef,
  Optional[String] $so_name                         = downcase($name),
  Optional[String] $ini_prefix                      = undef,
  Optional[String] $php_api_version                 = undef,
  String           $package_prefix                  = $::php::package_prefix,
  Boolean          $zend                            = false,
  Hash             $settings                        = {},
  Php::Sapi        $sapi                            = 'ALL',
  Variant[Boolean, String]       $settings_prefix   = false,
  Optional[Stdlib::AbsolutePath] $responsefile      = undef,
  Variant[String, Array[String]] $header_packages   = [],
  Variant[String, Array[String]] $compiler_packages = $::php::params::compiler_packages,
  Php::InstallOptions $install_options              = undef,
) {

  if ! defined(Class['php']) {
    warning('php::extension is private')
  }

  php::extension::install { $title:
    ensure            => $ensure,
    provider          => $provider,
    source            => $source,
    responsefile      => $responsefile,
    package_prefix    => $package_prefix,
    header_packages   => $header_packages,
    compiler_packages => $compiler_packages,
    install_options   => $install_options,
  }

  # PEAR packages don't require any further configuration, they just need to "be there".
  if $provider != 'pear' {
    php::extension::config { $title:
      ensure          => $ensure,
      provider        => $provider,
      so_name         => $so_name,
      ini_prefix      => $ini_prefix,
      php_api_version => $php_api_version,
      zend            => $zend,
      settings        => $settings,
      settings_prefix => $settings_prefix,
      sapi            => $sapi,
      subscribe       => Php::Extension::Install[$title],
    }
  }
}

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
#   Hash of parameters for the specific extension, which will be written to the extensions config file by
#   php::extension::config or a hash of mutliple settings files, each with parameters
#   (multifile_settings must be true)
#   (f.ex. {p => '..'} or {'bz2' => {..}, {'math' => {...}})
#
# [*multifile_settings*]
#   Set this to true if you specify multiple setting files in *settings*. This must be used when the PHP package
#   distribution bundles extensions in a single package (like 'common' bundles extensions 'bz2', ...) and each of
#   the extension comes with a separate settings file.
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
  Optional[String] $so_name                         = undef,
  Optional[String] $ini_prefix                      = undef,
  Optional[String] $php_api_version                 = undef,
  String           $package_prefix                  = $php::package_prefix,
  Boolean          $zend                            = false,
  Variant[Hash, Hash[String, Hash]] $settings       = {},
  Boolean          $multifile_settings              = false,
  Php::Sapi        $sapi                            = 'ALL',
  Variant[Boolean, String]       $settings_prefix   = false,
  Optional[Stdlib::AbsolutePath] $responsefile      = undef,
  Variant[String, Array[String]] $header_packages   = [],
  Variant[String, Array[String]] $compiler_packages = $php::params::compiler_packages,
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
    $_settings = $multifile_settings ? {
      true  => $settings,
      false => { downcase($title) => $settings } # emulate a hash if no multifile settings
    }

    $_settings.each |$settings_name, $settings_hash| {
      if $so_name {
        $so_name = $multifile_settings ? {
          true  => downcase($settings_name),
          false => pick(downcase($so_name), downcase($name), downcase($settings_name)),
        }
      } else {
        $so_name = $multifile_settings ? {
          true  => downcase($settings_name),
          false => pick(downcase($name), downcase($settings_name)),
        }
      }

      php::extension::config { $settings_name:
        ensure          => $ensure,
        provider        => $provider,
        so_name         => $so_name,
        ini_prefix      => $ini_prefix,
        php_api_version => $php_api_version,
        zend            => $zend,
        settings        => $settings_hash,
        settings_prefix => $settings_prefix,
        sapi            => $sapi,
        subscribe       => Php::Extension::Install[$title],
      }
    }
  }
}

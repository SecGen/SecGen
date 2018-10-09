# PHP globals class
#
# === Parameters
#
# [*php_version*]
#   The version of php.
#
# [*config_root*]
#   The configuration root directory.
#
# [*fpm_pid_file*]
#   Path to pid file for fpm
#
# [*rhscl_mode*]
#   The mode specifies the specifics in paths for the various RedHat SCL environments so that the module is configured
#   correctly on their pathnames.
#
#   Valid modes are: 'rhscl', 'remi'
#

class php::globals (

  Optional[Pattern[/^(rh-)?(php)?[57](\.)?[0-9]/]] $php_version = undef,
  Optional[Stdlib::Absolutepath] $config_root   = undef,
  Optional[Stdlib::Absolutepath] $fpm_pid_file  = undef,
  $rhscl_mode   = undef,
) {

  $default_php_version = $facts['os']['name'] ? {
    'Debian' => $facts['os']['release']['major'] ? {
      '9' => '7.0',
      default => '5.x',
    },
    'Ubuntu' => $facts['os']['release']['major'] ? {
      '18.04' => '7.2',
      '16.04' => '7.0',
      default => '5.x',
    },
    default => '5.x',
  }

  $globals_php_version = pick($php_version, $default_php_version)

  case $facts['os']['family'] {
    'Debian': {
      if $facts['os']['name'] == 'Ubuntu' {
        case $globals_php_version {
          /^5\.4/: {
            $default_config_root = '/etc/php5'
            $default_fpm_pid_file = "/var/run/php/php${globals_php_version}-fpm.pid"
            $fpm_error_log = '/var/log/php5-fpm.log'
            $fpm_service_name = 'php5-fpm'
            $ext_tool_enable = '/usr/sbin/php5enmod'
            $ext_tool_query = '/usr/sbin/php5query'
            $package_prefix = 'php5-'
          }
          /^[57].[0-9]/: {
            $default_config_root = "/etc/php/${globals_php_version}"
            $default_fpm_pid_file = "/var/run/php/php${globals_php_version}-fpm.pid"
            $fpm_error_log = "/var/log/php${globals_php_version}-fpm.log"
            $fpm_service_name = "php${globals_php_version}-fpm"
            $ext_tool_enable = "/usr/sbin/phpenmod -v ${globals_php_version}"
            $ext_tool_query = "/usr/sbin/phpquery -v ${globals_php_version}"
            $package_prefix = "php${globals_php_version}-"
          }
          default: {
            # Default php installation from Ubuntu official repository use the following paths until 16.04
            # For PPA please use the $php_version to override it.
            $default_config_root = '/etc/php5'
            $default_fpm_pid_file = '/var/run/php5-fpm.pid'
            $fpm_error_log = '/var/log/php5-fpm.log'
            $fpm_service_name = 'php5-fpm'
            $ext_tool_enable = '/usr/sbin/php5enmod'
            $ext_tool_query = '/usr/sbin/php5query'
            $package_prefix = 'php5-'
          }
        }
      } else {
        case $globals_php_version {
          /^7\.[0-9]/: {
            $default_config_root  = "/etc/php/${globals_php_version}"
            $default_fpm_pid_file = "/var/run/php/php${globals_php_version}-fpm.pid"
            $fpm_error_log        = "/var/log/php${globals_php_version}-fpm.log"
            $fpm_service_name     = "php${globals_php_version}-fpm"
            $ext_tool_enable      = "/usr/sbin/phpenmod -v ${globals_php_version}"
            $ext_tool_query       = "/usr/sbin/phpquery -v ${globals_php_version}"
            $package_prefix       = "php${globals_php_version}-"
          }
          default: {
            $default_config_root = '/etc/php5'
            $default_fpm_pid_file = '/var/run/php5-fpm.pid'
            $fpm_error_log = '/var/log/php5-fpm.log'
            $fpm_service_name = 'php5-fpm'
            $ext_tool_enable = '/usr/sbin/php5enmod'
            $ext_tool_query = '/usr/sbin/php5query'
            $package_prefix = 'php5-'
          }
        }
      }
    }
    'Suse': {
      case $globals_php_version {
        /^7/: {
          $default_config_root = '/etc/php7'
          $package_prefix = 'php7-'
          $default_fpm_pid_file = '/var/run/php7-fpm.pid'
          $fpm_error_log = '/var/log/php7-fpm.log'
        }
        default: {
          $default_config_root = '/etc/php5'
          $package_prefix = 'php5-'
          $default_fpm_pid_file = '/var/run/php5-fpm.pid'
          $fpm_error_log = '/var/log/php5-fpm.log'
        }
      }
    }
    'RedHat': {
      case $rhscl_mode {
        'remi': {
          $rhscl_root             = "/opt/remi/${php_version}/root"
          $default_config_root    = "/etc/opt/remi/${php_version}"
          $default_fpm_pid_file   = '/var/run/php-fpm/php-fpm.pid'
          $package_prefix         = "${php_version}-php-"
          $fpm_service_name       = "${php_version}-php-fpm"
        }
        'rhscl': {
          $rhscl_root             = "/opt/rh/${php_version}/root"
          $default_config_root    = "/etc/opt/rh/${php_version}" # rhscl registers contents by copy in /etc/opt/rh
          $default_fpm_pid_file   = "/var/opt/rh/${php_version}/run/php-fpm/php-fpm.pid"
          $package_prefix         = "${php_version}-php-"
          $fpm_service_name       = "${php_version}-php-fpm"
        }
        undef: {
          $default_config_root    = '/etc/php.d'
          $default_fpm_pid_file   = '/var/run/php-fpm/php-fpm.pid'
          $fpm_service_name       = undef
          $package_prefix         = undef
        }
        default: {
          fail("Unsupported rhscl_mode '${rhscl_mode}'")
        }
      }
    }
    'FreeBSD': {
      $default_config_root  = '/usr/local/etc'
      $default_fpm_pid_file = '/var/run/php-fpm.pid'
      $fpm_service_name     = undef
    }
    'Archlinux': {
      $default_config_root  =  '/etc/php'
      $default_fpm_pid_file = '/run/php-fpm/php-fpm.pid'
    }
    default: {
      fail("Unsupported osfamily: ${facts['os']['family']}")
    }
  }

  $globals_config_root    = pick($config_root, $default_config_root)
  $globals_fpm_pid_file   = pick($fpm_pid_file, $default_fpm_pid_file)
}

# Configure php-fpm service
#
# === Parameters
#
# [*config_file*]
#   The path to the fpm config file
#
# [*user*]
#   The user that runs php-fpm
#
# [*group*]
#   The group that runs php-fpm
#
# [*inifile*]
#   The path to ini file
#
# [*settings*]
#   Nested hash of key => value to apply to php.ini
#
# [*pool_base_dir*]
#   The folder that contains the php-fpm pool configs
#
# [*pool_purge*]
#   Whether to purge pool config files not created
#   by this module
#
# [*error_log*]
#   Path to error log file. If it's set to "syslog", log is
#   sent to syslogd instead of being written in a local file.
#
# [*log_level*]
#   The php-fpm log level
#
# [*emergency_restart_threshold*]
#   The php-fpm emergency_restart_threshold
#
# [*emergency_restart_interval*]
#   The php-fpm emergency_restart_interval
#
# [*process_control_timeout*]
#   The php-fpm process_control_timeout
#
# [*process_max*]
#   The maximum number of processes FPM will fork.
#
# [*rlimit_files*]
#   Set open file descriptor rlimit for the master process.
#
# [*systemd_interval*]
#   The interval between health report notification to systemd
#
# [*log_owner*]
#   The php-fpm log owner
#
# [*log_group*]
#   The group owning php-fpm logs
#
# [*log_dir_mode*]
#   The octal mode of the directory
#
# [*syslog_facility*]
#   Used to specify what type of program is logging the message
#
# [*syslog_ident*]
#   Prepended to every message
#
# [*root_group*]
#   UNIX group of the root user
#
# [*pid_file*]
#   Path to fpm pid file
#
class php::fpm::config(
  $config_file                                                          = $::php::params::fpm_config_file,
  String $user                                                          = $::php::params::fpm_user,
  String $group                                                         = $::php::params::fpm_group,
  String $inifile                                                       = $::php::params::fpm_inifile,
  $pid_file                                                             = $::php::params::fpm_pid_file,
  Hash $settings                                                        = {},
  Stdlib::Absolutepath $pool_base_dir                                   = $::php::params::fpm_pool_dir,
  $pool_purge                                                           = false,
  String $error_log                                                     = $::php::params::fpm_error_log,
  String $log_level                                                     = 'notice',
  Integer $emergency_restart_threshold                                  = 0,
  Variant[Integer, Pattern[/^\d+[smhd]?$/]] $emergency_restart_interval = 0,
  Variant[Integer, Pattern[/^\d+[smhd]?$/]] $process_control_timeout    = 0,
  Integer $process_max                                                  = 0,
  $rlimit_files                                                         = undef,
  Optional[Variant[Integer,Pattern[/^\d+[smhd]?$/]]] $systemd_interval  = undef,
  String $log_owner                                                     = $::php::params::fpm_user,
  String $log_group                                                     = $::php::params::fpm_group,
  Pattern[/^\d+$/] $log_dir_mode                                        = '0770',
  $root_group                                                           = $::php::params::root_group,
  String $syslog_facility                                               = 'daemon',
  String $syslog_ident                                                  = 'php-fpm',
) inherits ::php::params {

  if $caller_module_name != $module_name {
    warning('php::fpm::config is private')
  }

  # Hack-ish to default to user for group too
  $log_group_final = $log_group ? {
    undef   => $log_owner,
    default => $log_group,
  }

  file { $config_file:
    ensure  => file,
    content => template('php/fpm/php-fpm.conf.erb'),
    owner   => root,
    group   => $root_group,
    mode    => '0644',
  }

  file { $pool_base_dir:
    ensure => directory,
    owner  => root,
    group  => $root_group,
    mode   => '0755',
  }

  if $pool_purge {
    File[$pool_base_dir] {
      purge   => true,
      recurse => true,
    }
  }

  ::php::config { 'fpm':
    file   => $inifile,
    config => $settings,
  }
}

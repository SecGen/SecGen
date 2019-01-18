# @summary Main class, includes all other classes.
#
# @see https://docs.puppetlabs.com/references/latest/function.html#createresources for the create resource function
#
# @param provider
#   Specifies the provider that should be used by apt::update.
#
# @param keyserver
#   Specifies a keyserver to provide the GPG key. Valid options: a string containing a domain name or a full URL (http://, https://, or 
#   hkp://).
#
# @param ppa_options
#   Supplies options to be passed to the `add-apt-repository` command. 
#
# @param ppa_package
#   Names the package that provides the `apt-add-repository` command.
#
# @param backports
#   Specifies some of the default parameters used by apt::backports. Valid options: a hash made up from the following keys:
#
# @option backports [String] :location 
#   See apt::backports for documentation.
#
# @option backports [String] :repos 
#   See apt::backports for documentation.
#
# @option backports [String] :key 
#   See apt::backports for documentation.
#
# @param confs
#   Creates new `apt::conf` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param update
#   Configures various update settings. Valid options: a hash made up from the following keys:
#
# @option update [String] :frequency
#   Specifies how often to run `apt-get update`. If the exec resource `apt_update` is notified, `apt-get update` runs regardless of this value. 
#   Valid options: 'always' (at every Puppet run); 'daily' (if the value of `apt_update_last_success` is less than current epoch time minus 86400); 
#   'weekly' (if the value of `apt_update_last_success` is less than current epoch time minus 604800); and 'reluctantly' (only if the exec resource 
#   `apt_update` is notified). Default: 'reluctantly'.
#
# @option update [Integer] :loglevel
#   Specifies the log level of logs outputted to the console. Default: undef.
#
# @option update [Integer] :timeout
#   Specifies how long to wait for the update to complete before canceling it. Valid options: an integer, in seconds. Default: undef.
#
# @option update [Integer] :tries
#    Specifies how many times to retry the update after receiving a DNS or HTTP error. Default: undef.
#
# @param purge
#   Specifies whether to purge any existing settings that aren't managed by Puppet. Valid options: a hash made up from the following keys:
#
# @option purge [Boolean] :sources.list
#   Specifies whether to purge any unmanaged entries from sources.list. Default false.
#
# @option purge [Boolean] :sources.list.d
#   Specifies whether to purge any unmanaged entries from sources.list.d. Default false.
#
# @option purge [Boolean] :preferences
#   Specifies whether to purge any unmanaged entries from preferences. Default false.
#
# @option purge [Boolean] :preferences.d.
#   Specifies whether to purge any unmanaged entries from preferences.d. Default false.
#
# @param proxy
#   Configures Apt to connect to a proxy server. Valid options: a hash matching the locally defined type apt::proxy.
#
# @param sources
#   Creates new `apt::source` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param keys
#   Creates new `apt::key` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param ppas
#   Creates new `apt::ppa` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param pins
#   Creates new `apt::pin` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param settings
#   Creates new `apt::setting` resources. Valid options: a hash to be passed to the create_resources function linked above.
#
# @param auth_conf_entries
#   An optional array of login configuration settings (hashes) that are recorded in the file /etc/apt/auth.conf. This file has a netrc-like 
#   format (similar to what curl uses) and contains the login configuration for APT sources and proxies that require authentication. See 
#   https://manpages.debian.org/testing/apt/apt_auth.conf.5.en.html for details. If specified each hash must contain the keys machine, login and 
#   password and no others.
#
# @param root
#   Specifies root directory of Apt executable.
#
# @param sources_list
#   Specifies the path of the sources_list file to use. 
#
# @param sources_list_d
#   Specifies the path of the sources_list.d file to use. 
#
# @param conf_d
#   Specifies the path of the conf.d file to use. 
#
# @param preferences
#   Specifies the path of the preferences file to use. 
#
# @param preferences_d
#   Specifies the path of the preferences.d file to use. 
#
# @param config_files
#   A hash made up of the various configuration files used by Apt.
#
class apt (
  Hash $update_defaults         = $apt::params::update_defaults,
  Hash $purge_defaults          = $apt::params::purge_defaults,
  Hash $proxy_defaults          = $apt::params::proxy_defaults,
  Hash $include_defaults        = $apt::params::include_defaults,
  String $provider              = $apt::params::provider,
  String $keyserver             = $apt::params::keyserver,
  Optional[String] $ppa_options = $apt::params::ppa_options,
  Optional[String] $ppa_package = $apt::params::ppa_package,
  Optional[Hash] $backports     = $apt::params::backports,
  Hash $confs                   = $apt::params::confs,
  Hash $update                  = $apt::params::update,
  Hash $purge                   = $apt::params::purge,
  Apt::Proxy $proxy             = $apt::params::proxy,
  Hash $sources                 = $apt::params::sources,
  Hash $keys                    = $apt::params::keys,
  Hash $ppas                    = $apt::params::ppas,
  Hash $pins                    = $apt::params::pins,
  Hash $settings                = $apt::params::settings,
  Array[Apt::Auth_conf_entry]
    $auth_conf_entries          = $apt::params::auth_conf_entries,
  String $root                  = $apt::params::root,
  String $sources_list          = $apt::params::sources_list,
  String $sources_list_d        = $apt::params::sources_list_d,
  String $conf_d                = $apt::params::conf_d,
  String $preferences           = $apt::params::preferences,
  String $preferences_d         = $apt::params::preferences_d,
  Hash $config_files            = $apt::params::config_files,
  Hash $source_key_defaults     = $apt::params::source_key_defaults,
) inherits apt::params {

  if $facts['osfamily'] != 'Debian' {
    fail(translate('This module only works on Debian or derivatives like Ubuntu'))
  }

  if $update['frequency'] {
    assert_type(
      Enum['always','daily','weekly','reluctantly'],
      $update['frequency'],
    )
  }
  if $update['timeout'] {
    assert_type(Integer, $update['timeout'])
  }
  if $update['tries'] {
    assert_type(Integer, $update['tries'])
  }

  $_update = merge($::apt::update_defaults, $update)
  include ::apt::update

  if $purge['sources.list'] {
    assert_type(Boolean, $purge['sources.list'])
  }
  if $purge['sources.list.d'] {
    assert_type(Boolean, $purge['sources.list.d'])
  }
  if $purge['preferences'] {
    assert_type(Boolean, $purge['preferences'])
  }
  if $purge['preferences.d'] {
    assert_type(Boolean, $purge['preferences.d'])
  }

  $_purge = merge($::apt::purge_defaults, $purge)
  $_proxy = merge($apt::proxy_defaults, $proxy)

  $confheadertmp = epp('apt/_conf_header.epp')
  $proxytmp = epp('apt/proxy.epp', {'proxies' => $_proxy})
  $updatestamptmp = epp('apt/15update-stamp.epp')

  if $_proxy['ensure'] == 'absent' or $_proxy['host'] {
    apt::setting { 'conf-proxy':
      ensure   => $_proxy['ensure'],
      priority => '01',
      content  => "${confheadertmp}${proxytmp}",
    }
  }

  $sources_list_content = $_purge['sources.list'] ? {
    true    => "# Repos managed by puppet.\n",
    default => undef,
  }

  $preferences_ensure = $_purge['preferences'] ? {
    true    => absent,
    default => file,
  }

  if $_update['frequency'] == 'always' {
    Exec <| title=='apt_update' |> {
      refreshonly => false,
    }
  }

  apt::setting { 'conf-update-stamp':
    priority => 15,
    content  => "${confheadertmp}${updatestamptmp}",
  }

  file { 'sources.list':
    ensure  => file,
    path    => $::apt::sources_list,
    owner   => root,
    group   => root,
    mode    => '0644',
    content => $sources_list_content,
    notify  => Class['apt::update'],
  }

  file { 'sources.list.d':
    ensure  => directory,
    path    => $::apt::sources_list_d,
    owner   => root,
    group   => root,
    mode    => '0644',
    purge   => $_purge['sources.list.d'],
    recurse => $_purge['sources.list.d'],
    notify  => Class['apt::update'],
  }

  file { 'preferences':
    ensure => $preferences_ensure,
    path   => $::apt::preferences,
    owner  => root,
    group  => root,
    mode   => '0644',
    notify => Class['apt::update'],
  }

  file { 'preferences.d':
    ensure  => directory,
    path    => $::apt::preferences_d,
    owner   => root,
    group   => root,
    mode    => '0644',
    purge   => $_purge['preferences.d'],
    recurse => $_purge['preferences.d'],
    notify  => Class['apt::update'],
  }

  if $confs {
    create_resources('apt::conf', $confs)
  }
  # manage sources if present
  if $sources {
    create_resources('apt::source', $sources)
  }
  # manage keys if present
  if $keys {
    create_resources('apt::key', $keys)
  }
  # manage ppas if present
  if $ppas {
    create_resources('apt::ppa', $ppas)
  }
  # manage settings if present
  if $settings {
    create_resources('apt::setting', $settings)
  }

  $auth_conf_ensure = $auth_conf_entries ? {
    []      => 'absent',
    default => 'present',
  }

  $auth_conf_tmp = epp('apt/auth_conf.epp')

  file { '/etc/apt/auth.conf':
    ensure  => $auth_conf_ensure,
    owner   => 'root',
    group   => 'root',
    mode    => '0600',
    content => "${confheadertmp}${auth_conf_tmp}",
    notify  => Class['apt::update'],
  }

  # manage pins if present
  if $pins {
    create_resources('apt::pin', $pins)
  }

  # required for adding GPG keys on Debian 9 (and derivatives)
  ensure_packages(['dirmngr'])
}

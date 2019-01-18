# @summary Manages PPA repositories using `add-apt-repository`. Not supported on Debian.
#
# @example Example declaration of an Apt PPA
#   apt::ppa{ 'ppa:openstack-ppa/bleeding-edge': }
#
# @param ensure
#   Specifies whether the PPA should exist. Valid options: 'present' and 'absent'. 
#
# @param options
#   Supplies options to be passed to the `add-apt-repository` command. Default: '-y'.
#
# @param release
#   Optional if lsb-release is installed (unless you're using a different release than indicated by lsb-release, e.g., Linux Mint). 
#   Specifies the operating system of your node. Valid options: a string containing a valid LSB distribution codename.
#
# @param package_name
#   Names the package that provides the `apt-add-repository` command. Default: 'software-properties-common'.
#
# @param package_manage
#   Specifies whether Puppet should manage the package that provides `apt-add-repository`.
#
define apt::ppa(
  String $ensure                 = 'present',
  Optional[String] $options      = $::apt::ppa_options,
  Optional[String] $release      = $facts['lsbdistcodename'],
  Optional[String] $package_name = $::apt::ppa_package,
  Boolean $package_manage        = false,
) {
  unless $release {
    fail(translate('lsbdistcodename fact not available: release parameter required'))
  }

  if $facts['lsbdistid'] == 'Debian' {
    fail(translate('apt::ppa is not currently supported on Debian.'))
  }

  if versioncmp($facts['lsbdistrelease'], '14.10') >= 0 {
    $distid = downcase($facts['lsbdistid'])
    $dash_filename = regsubst($name, '^ppa:([^/]+)/(.+)$', "\\1-${distid}-\\2")
    $underscore_filename = regsubst($name, '^ppa:([^/]+)/(.+)$', "\\1_${distid}_\\2")
  } else {
    $dash_filename = regsubst($name, '^ppa:([^/]+)/(.+)$', "\\1-\\2")
    $underscore_filename = regsubst($name, '^ppa:([^/]+)/(.+)$', "\\1_\\2")
  }

  $dash_filename_no_slashes      = regsubst($dash_filename, '/', '-', 'G')
  $dash_filename_no_specialchars = regsubst($dash_filename_no_slashes, '[\.\+]', '_', 'G')
  $underscore_filename_no_slashes      = regsubst($underscore_filename, '/', '-', 'G')
  $underscore_filename_no_specialchars = regsubst($underscore_filename_no_slashes, '[\.\+]', '_', 'G')

  $sources_list_d_filename  = "${dash_filename_no_specialchars}-${release}.list"

  if versioncmp($facts['lsbdistrelease'], '15.10') >= 0 {
    $trusted_gpg_d_filename = "${underscore_filename_no_specialchars}.gpg"
  } else {
    $trusted_gpg_d_filename = "${dash_filename_no_specialchars}.gpg"
  }

  if $ensure == 'present' {
    if $package_manage {
      ensure_packages($package_name)
      $_require = [File['sources.list.d'], Package[$package_name]]
    } else {
      $_require = File['sources.list.d']
    }

    $_proxy = $::apt::_proxy
    if $_proxy['host'] {
      if $_proxy['https'] {
        $_proxy_env = ["http_proxy=http://${$_proxy['host']}:${$_proxy['port']}", "https_proxy=https://${$_proxy['host']}:${$_proxy['port']}"]
      } else {
        $_proxy_env = ["http_proxy=http://${$_proxy['host']}:${$_proxy['port']}"]
      }
    } else {
      $_proxy_env = []
    }

    exec { "add-apt-repository-${name}":
      environment => $_proxy_env,
      command     => "/usr/bin/add-apt-repository ${options} ${name} || (rm ${::apt::sources_list_d}/${sources_list_d_filename} && false)",
      unless      => "/usr/bin/test -f ${::apt::sources_list_d}/${sources_list_d_filename} && /usr/bin/test -f ${::apt::trusted_gpg_d}/${trusted_gpg_d_filename}",
      user        => 'root',
      logoutput   => 'on_failure',
      notify      => Class['apt::update'],
      require     => $_require,
    }

    file { "${::apt::sources_list_d}/${sources_list_d_filename}":
      ensure  => file,
      require => Exec["add-apt-repository-${name}"],
    }
  }
  else {
    file { "${::apt::sources_list_d}/${sources_list_d_filename}":
      ensure => 'absent',
      notify => Class['apt::update'],
    }
  }
}

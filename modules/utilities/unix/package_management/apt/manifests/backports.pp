# @summary Manages backports.
#
# @example Set up a backport for linuxmint qiana
#   apt::backports { 'qiana':
#     location => 'http://us.archive.ubuntu.com/ubuntu',
#     release  => 'trusty-backports',
#     repos    => 'main universe multiverse restricted',
#     key      => {
#       id     => '630239CC130E1A7FD81A27B140976EAF437D05B5',
#       server => 'hkps.pool.sks-keyservers.net',
#     },
#   }
#
# @param location
#   Specifies an Apt repository containing the backports to manage. Valid options: a string containing a URL. Default value for Debian and 
#   Ubuntu varies:
#
#   - Debian: 'http://deb.debian.org/debian'
#
#   - Ubuntu: 'http://archive.ubuntu.com/ubuntu'
#
# @param release
#   Specifies a distribution of the Apt repository containing the backports to manage. Used in populating the `source.list` configuration file. 
#   Default: on Debian and Ubuntu, '${lsbdistcodename}-backports'. We recommend keeping this default, except on other operating 
#   systems.
#
# @param repos
#   Specifies a component of the Apt repository containing the backports to manage. Used in populating the `source.list` configuration file. 
#   Default value for Debian and Ubuntu varies:
#   
#   - Debian: 'main contrib non-free'
#
#   - Ubuntu: 'main universe multiverse restricted'
#
# @param key
#   Specifies a key to authenticate the backports. Valid options: a string to be passed to the id parameter of the apt::key defined type, or a 
#   hash of parameter => value pairs to be passed to apt::key's id, server, content, source, and/or options parameters. Default value
#   for Debian and Ubuntu varies:
#
#   - Debian: 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553'
#
#   - Ubuntu: '630239CC130E1A7FD81A27B140976EAF437D05B5'
#
# @param pin
#   Specifies a pin priority for the backports. Valid options: a number or string to be passed to the `id` parameter of the `apt::pin` defined 
#   type, or a hash of `parameter => value` pairs to be passed to `apt::pin`'s corresponding parameters.
#
class apt::backports (
  Optional[String] $location                    = undef,
  Optional[String] $release                     = undef,
  Optional[String] $repos                       = undef,
  Optional[Variant[String, Hash]] $key          = undef,
  Optional[Variant[Integer, String, Hash]] $pin = 200,
){
  if $location {
    $_location = $location
  }
  if $release {
    $_release = $release
  }
  if $repos {
    $_repos = $repos
  }
  if $key {
    $_key = $key
  }
  if (!($facts['lsbdistid'] == 'Debian' or $facts['lsbdistid'] == 'Ubuntu')) {
    unless $location and $release and $repos and $key {
      fail(translate('If not on Debian or Ubuntu, you must explicitly pass location, release, repos, and key'))
    }
  }
  unless $location {
    $_location = $::apt::backports['location']
  }
  unless $release {
    $_release = "${facts['lsbdistcodename']}-backports"
  }
  unless $repos {
    $_repos = $::apt::backports['repos']
  }
  unless $key {
    $_key =  $::apt::backports['key']
  }

  if $pin =~ Hash {
    $_pin = $pin
  } elsif $pin =~ Numeric or $pin =~ String {
    # apt::source defaults to pinning to origin, but we should pin to release
    # for backports
    $_pin = {
      'priority' => $pin,
      'release'  => $_release,
    }
  } else {
    fail(translate('pin must be either a string, number or hash'))
  }

  apt::source { 'backports':
    location => $_location,
    release  => $_release,
    repos    => $_repos,
    key      => $_key,
    pin      => $_pin,
  }
}

# Configure debian apt repo
#
# === Parameters
#
# [*location*]
#   Location of the apt repository
#
# [*release*]
#   Release of the apt repository
#
# [*repos*]
#   Apt repository names
#
# [*include_src*]
#   Add source source repository
#
# [*key*]
#   Public key in apt::key format
#
# [*dotdeb*]
#   Enable special dotdeb handling
#
# [*sury*]
#   Enable special sury handling
#
class php::repo::debian(
  $location     = 'https://packages.dotdeb.org',
  $release      = 'wheezy-php56',
  $repos        = 'all',
  $include_src  = false,
  $key          = {
    'id'     => '6572BBEF1B5FF28B28B706837E3F070089DF5277',
    'source' => 'http://www.dotdeb.org/dotdeb.gpg',
  },
  $dotdeb       = true,
  $sury         = true,
) {

  assert_private()

  include '::apt'

  create_resources(::apt::key, { 'php::repo::debian' => {
    id     => $key['id'],
    source => $key['source'],
  }})

  ::apt::source { "source_php_${release}":
    location => $location,
    release  => $release,
    repos    => $repos,
    include  => {
      'src' => $include_src,
      'deb' => true,
    },
    require  => Apt::Key['php::repo::debian'],
  }

  if ($dotdeb) {
    # both repositories are required to work correctly
    # See: http://www.dotdeb.org/instructions/
    if $release == 'wheezy-php56' {
      ::apt::source { 'dotdeb-wheezy':
        location => $location,
        release  => 'wheezy',
        repos    => $repos,
        include  => {
          'src' => $include_src,
          'deb' => true,
        },
      }
    }
  }

  if ($sury and $php::globals::php_version == '7.1') {
    # Required packages for PHP 7.1 repository
    ensure_packages(['lsb-release', 'ca-certificates'], {'ensure' => 'present'})

    # Add PHP 7.1 key + repository
    apt::key { 'php::repo::debian-php71':
      id     => 'DF3D585DB8F0EB658690A554AC0E47584A7A714D',
      source => 'https://packages.sury.org/php/apt.gpg',
    }

    ::apt::source { 'source_php_71':
      location => 'https://packages.sury.org/php/',
      release  => $facts['os']['distro']['codename'],
      repos    => 'main',
      include  => {
        'src' => $include_src,
        'deb' => true,
      },
      require  => [
        Apt::Key['php::repo::debian-php71'],
        Package['apt-transport-https', 'lsb-release', 'ca-certificates']
      ],
    }
  }
}

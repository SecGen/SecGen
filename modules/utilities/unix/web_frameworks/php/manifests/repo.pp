# Configure package repository
#
class php::repo {

  $msg_no_repo = "No repo available for ${facts['os']['family']}/${facts['os']['name']}"

  case $facts['os']['family'] {
    'Debian': {
      # no contain here because apt does that already
      case $facts['os']['name'] {
        'Debian': {
          include ::php::repo::debian
        }
        'Ubuntu': {
          include ::php::repo::ubuntu
        }
        default: {
          fail($msg_no_repo)
        }
      }
    }
    'FreeBSD': {}
    'Suse': {
      contain ::php::repo::suse
    }
    'RedHat': {
      contain '::php::repo::redhat'
    }
    default: {
      fail($msg_no_repo)
    }
  }
}

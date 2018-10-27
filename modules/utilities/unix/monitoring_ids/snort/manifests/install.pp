class snort::install {

  # install rules and config via debian repo
  package { ['snort-rules-default','snort-common']:
    ensure => installed,
  } ->

  exec { 'install snort':
    path     => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    command  => '/bin/true',
    provider => shell,
    onlyif   => 'apt-get install -y snort; systemctl disable snort',
  }
}

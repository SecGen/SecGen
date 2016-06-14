exec { 'update':
  command => "/usr/bin/apt-get update -y",
}


class { 'java':
  distribution => 'jre',
}
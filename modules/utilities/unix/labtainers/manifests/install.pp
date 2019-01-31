class labtainers::install{
  # $json_inputs = base64('decode', $::base64_inputs)
  # $secgen_parameters = parsejson($json_inputs)
  # $server_ip = $secgen_parameters['server_ip'][0]
  # $port = $secgen_parameters['port'][0]


  # these are also installed by the install script, but good to use puppet where possible
  package { ['apt-transport-https', 'ca-certificates', 'curl', 'gnupg2', 'software-properties-common', 'python-pip', 'python-parse', 'python-netaddr', 'openssh-server', 'curl']:
    ensure   => 'installed',
  } ->

  # TODO: file permissions
  file { '/opt/labtainers':
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/labtainers/labtainer.files/',
    mode   => '0777',
    owner => 'root',
    group => 'root',
  } ->

  exec { 'install docker script':
    command  => '/opt/labtainers/install-labtainer.sh',
    provider => shell,
  }

}

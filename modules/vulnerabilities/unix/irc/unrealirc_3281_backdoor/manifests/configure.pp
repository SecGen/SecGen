class unrealirc_3281_backdoor::configure {

  $secgen_parameters = parsejson($::json_inputs)
  $user = $secgen_parameters['user'][0]
  $group = $secgen_parameters['group'][0]
  $motd = $secgen_parameters['motd'][0]
  $user_home = "/home/$user"

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'], }

  file { '/etc/init.d/unreal':
    ensure   => file,
    mode     => '0755',
    content  => template('unrealirc_3281_backdoor/unreal.erb'),
  }

  file { '/var/lib/unreal/unrealircd.conf':
    ensure   => file,
    mode     => '0600',
    content  => template('unrealirc_3281_backdoor/unrealircd.conf.erb'),
  }

  # Update message of the day w/ param
  file { '/var/lib/unreal/ircd.motd2':
    ensure => file,
    content => $motd
  }

  exec { 'update-motd':
    cwd => '/var/lib/unreal/',
    command => 'cat ircd.motd2 > ircd.motd'
  }

  # Leak file and share extras
  file { "$user_home/$leaked_filename":
    ensure  => present,
    owner   => $user,
    group   => $group,
    mode    => '0777',
    content  => template('unrealirc_3281_backdoor/overshare.erb')
  }
}
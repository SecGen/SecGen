class distcc_exec::config{
  $secgen_parameters = parsejson($::json_inputs)
  $leaked_filename = $secgen_parameters['leaked_filename'][0]

  file { '/etc/default/distcc':
    require => Package['distcc'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('distcc_exec/distcc.erb')
  }

  # distccd home directory
  file { '/home/distccd/':
    ensure => directory,
    owner => 'distccd',
    mode  =>  '0750',
  }

  #exec usermod home directory for distccd
  exec { 'change-home-dir':
    path => ['/usr/bin/', '/usr/sbin'],
    command => 'usermod -d /home/distccd distccd'
  }

  # Overread
    file { "/home/distccd/$leaked_filename":
      ensure  => present,
      owner   => 'distccd',
      mode    => '0750',
      content  => template('distcc_exec/overshare.erb')
    }
}
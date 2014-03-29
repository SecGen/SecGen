class distcc::config {

  package { 'distcc':
      ensure => installed
  }


  file { '/etc/default/distcc':
    require => Package['distcc'],
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('distcc/distcc.erb')
  }


  service { 'distcc':
    ensure => running,
}
}



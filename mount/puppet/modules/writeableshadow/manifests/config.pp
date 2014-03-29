class writeableshadow::config {

  file { '/etc/shadow':
    ensure  => present,
    mode    => '0777',
  }


}

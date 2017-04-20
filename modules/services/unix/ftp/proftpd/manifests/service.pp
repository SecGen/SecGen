class proftpd::service {
  service { 'proftpd':
    ensure  => running,
    enable  => true,
    require => File['/etc/proftpd/proftpd.conf'],
  }
}
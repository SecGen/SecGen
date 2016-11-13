class proftpd::configure {
  file { '/etc/proftpd/proftpd.conf':
    ensure   => present,
    owner    => 'root',
    group    => 'root',
    mode     => '0644',
    content  => template('proftpd/proftpd.erb')
  }
}
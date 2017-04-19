class proftpd::configure {
  $json_inputs = base64('decode', $::base64_inputs)
  file { '/etc/proftpd/proftpd.conf':
    notify   => Service['proftpd'],
    ensure   => present,
    owner    => 'root',
    group    => 'root',
    mode     => '0644',
    content  => template('proftpd/proftpd.erb'),
  }
}
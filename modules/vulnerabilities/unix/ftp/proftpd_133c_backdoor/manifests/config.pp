class proftpd_133c_backdoor::config {
  $json_inputs = base64('decode', $::base64_inputs)
  file { '/etc/proftpd/proftpd.conf':
    ensure   => present,
    owner    => 'root',
    group    => 'root',
    mode     => '0644',
    content  => template('proftpd_133c_backdoor/proftpd.erb')
  }
}
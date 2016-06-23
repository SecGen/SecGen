class proftpd_133c_backdoor::config {
  file { '/etc/proftpd/proftpd.conf':
    ensure   => present,
    owner    => 'root',
    group    => 'root',
    mode     => '0644',
    content  => template('proftpd_133c_backdoor/proftpd.erb')
  }
}
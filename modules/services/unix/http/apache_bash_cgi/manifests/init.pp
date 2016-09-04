class apache_bash_cgi::init {
  file { '/usr/lib/cgi-bin/test.cgi':
    source => 'puppet:///modules/apache_bash_cgi/test.cgi',
    ensure => present,
    mode => '755',
  }
}
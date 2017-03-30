class btn_xss_challenge::install {

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  package { ['php5','php5-mysql']:
    ensure => installed,
  }

  file { 'btn_xss-copy_files':
    path => '/var/www/challenge_dir',
    source => "puppet:///modules/btn_xss_challenge",
    recurse => true,
  }
}
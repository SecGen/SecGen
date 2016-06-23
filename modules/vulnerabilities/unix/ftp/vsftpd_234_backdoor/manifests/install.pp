  #copies and unpacks vsftpd_234_backdoor saves it to usr/local/sbin and executes it for startup
class vsftpd_234_backdoor::install {

  # file { '/tmp/vsftpd-2.3.4':
  #   path         => '/tmp/vsftpd-2.3.4',
  #   ensure       => directory,
  #   source       => 'puppet:///modules/vsftpd_234_backdoor',
  #   recurse      => true,
  #
  # }
  file { '/tmp/src':
    ensure => directory,
    path => '/tmp/src',
    source => 'puppet:///modules/vsftpd_234_backdoor',
    recurse => 'true',
    mode => '777'
  }

  exec { 'unzip-vsftpd':
    command     => 'tar -xzf /tmp/src/vsftpd-2.3.4.tar.gz',
    path => '/bin',
    cwd => '/tmp',
    # creates     => "/home/vagrant/vsftpd-2.3.4/vsftpd",
    # notify   => Exec['make-vsftpd']
  }

  # TODO: FIXME this is broken
  # exec { 'make-vsftpd':
  #   command     => '/usr/bin/make',
  #   cwd         => "/tmp/src/vsftpd-2.3.4",
  #   creates     => "/tmp/src/vsftpd-2.3.4/vsftpd",
  #   notify   => Exec['copy-vsftpd'],
  #   require     => Exec["unzip-vsftpd"],
  # }
  #
  # exec { 'copy-vsftpd':
  #   command     => '/usr/bin/make install',
  #   cwd         => "/tmp/src/vsftpd-2.3.4",
  #   # creates     => "/usr/local/sbin/vsftpd",
  #   notify   => User['ftp'],
  #   require     => Exec["make-vsftpd"],
  # }
  #
  # # exec { 'copy-vsftpd':
  # #   command     => '/tmp/src/copyvsftpd.sh',
  # #   cwd         => "/tmp/src/",
  # #   creates     => "/usr/local/sbin/vsftpd",
  # #   notify   => User['ftp'],
  # #   require     => Exec["make-vsftpd"],
  # # }
  #
  # user { 'ftp':
  #   ensure     => present,
  #   uid        => '507',
  #   gid        => 'root',
  #   shell      => '/bin/zsh',
  #   home       => '/var/ftp',
  #   notify   => Exec['start-vsftpd'],
  #   require     => Exec["copy-vsftpd"],
  #   managehome => true
  # }
  #
  # exec { 'start-vsftpd':
  #   command     => '/tmp/vsftpd-2.3.4/startvsftpd.sh',
  #   require     => User["ftp"],
  # }
}




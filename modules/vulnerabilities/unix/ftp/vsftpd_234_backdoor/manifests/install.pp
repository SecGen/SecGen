class vsftpd_234_backdoor::install {

  # Install dependencies
  package { ['libssl-dev' ,'libpam0g-dev']:
    ensure => installed,
  }

  # Required directories
  file { ['/usr/share/empty','/var/ftp','/usr/local/man/man5/', '/usr/local/man/man8/']:
    ensure => directory,
    owner  => root,
    mode   => '0755'
  }

  # Require tarball
  file { '/usr/local/src/vsftpd-2.3.4.tar.gz':
    ensure => file,
    source => 'puppet:///modules/vsftpd_234_backdoor/vsftpd-2.3.4.tar.gz',
  }

  # Unpack tar
  exec { 'unzip-vsftpd':
    require     => Package['libssl-dev' ,'libpam0g-dev'],
    command     => '/bin/tar -xzf /usr/local/src/vsftpd-2.3.4.tar.gz',
    cwd         => '/usr/local/src',
    creates     => '/usr/local/src/vsftpd-2.3.4/',
  }

  # Use module Makefile
  file { ['/usr/local/src/vsftpd-2.3.4/Makefile']:
    require  => Exec['unzip-vsftpd'],
    ensure   => file,
    content  => file('vsftpd_234_backdoor/Makefile'),
  }

  # Make
  exec { 'make-vsftpd':
    require     => File['/etc/vsftpd.conf', '/usr/local/man/man5/vsftpd.conf.5', '/usr/local/man/man8/vsftpd.8'],
    command     => '/usr/bin/make',
    cwd         => '/usr/local/src/vsftpd-2.3.4'
  }

  # Make install
  exec { 'make-install-vsftpd':
    require     => Exec['make-vsftpd'],
    command     => '/usr/bin/make install',
    cwd         => '/usr/local/src/vsftpd-2.3.4'
  }

  file { ['/usr/local/sbin/vsftpd']:
    require => Exec['make-install-vsftpd'],
    ensure  => file,
    source  => '/usr/local/src/vsftpd-2.3.4/vsftpd',
  }

  # init.d file
  file { ['/etc/init.d/vsftpd']:
    require => Exec['make-install-vsftpd'],
    ensure  => file,
    source  => 'puppet:///modules/vsftpd_234_backdoor/vsftpd_init.d',
    mode   => '0755',
  }
}




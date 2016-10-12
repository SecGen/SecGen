class chkrootkit::install {
  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  $archive = 'chkrootkit-0.49.tar.gz'

  file { "/usr/local/$archive":
    ensure => file,
    source => "puppet:///modules/chkrootkit/$archive",
  }

  exec { 'unpack-chkrootkit':
    cwd     => '/usr/local/',
    command => "tar -xzf $archive",
  }

  exec { 'make-chkrootkit':
    cwd => '/usr/local/chkrootkit-0.49/',
    command => 'make sense',
  }

  file { '/usr/sbin/chkrootkit':
    ensure => 'link',
    target => '/usr/local/chkrootkit-0.49/chkrootkit'
  }

  exec { "remove-$archive":
    require => Exec['unpack-chkrootkit'],
    command => "rm /usr/local/$archive",
  }
}
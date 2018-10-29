class metactf::install {

  file { '/usr/lib/metactf':
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/metactf/repository',
  }

}
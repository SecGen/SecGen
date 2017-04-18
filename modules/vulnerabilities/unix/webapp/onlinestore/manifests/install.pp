class onlinestore::install {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)

  # Parse out parameters
  $db_flag = $secgen_parameters['strings_to_leak'][0]
  $admin_flag = $secgen_parameters['strings_to_leak'][1]
  $root_file_flag = $secgen_parameters['strings_to_leak'][2]
  $black_market_flag = $secgen_parameters['strings_to_leak'][3]

  $docroot = '/var/www'
  $db_username = 'csecvm'
  $db_password = 'H93AtG6akq'

  package { ['mysql-client','php5-mysql']:
    ensure => 'installed',
  }

  file { "/var/www/index.html":
    ensure => absent,
  }

  file { "/tmp/www-data.tar.gz":
    owner  => root,
    group  => root,
    mode   => '0600',
    ensure => file,
    source => 'puppet:///modules/onlinestore/www-data.tar.gz',
    notify => Exec['unpack'],
  }

  exec { 'unpack':
    cwd     => "$docroot",
    command => "tar -xzf /tmp/www-data.tar.gz && chown -R www-data:www-data $docroot && chmod 0600 $docroot/mysql.php",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    notify => Exec['setup_mysql'],
  }

  file { "/tmp/csecvm.sql":
    owner  => root,
    group  => root,
    mode   => '0600',
    ensure => file,
    source => 'puppet:///modules/onlinestore/csecvm.sql',
  }

  file { "/tmp/mysql_setup.sh":
    owner  => root,
    group  => root,
    mode   => '0700',
    ensure => file,
    source => 'puppet:///modules/onlinestore/mysql_setup.sh',
    notify => Exec['setup_mysql'],
  }

  exec { 'setup_mysql':
    cwd     => "/tmp",
    command => "sudo ./mysql_setup.sh $db_username $db_password $db_flag",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    notify => Exec['create_root_flag'],
  }

  exec { 'create_root_flag':
    cwd     => "/home/vagrant",
    command => "echo '$root_file_flag' > /webroot && chown -f root:root /webroot && chmod -f 0600 /webroot",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    notify => Exec['create_admin_flag'],
  }

  exec { 'create_admin_flag':
    cwd     => "$docroot",
    command => "echo '$admin_flag' > ./.admin && chown -f www-data:www-data ./.admin && chmod -f 0600 ./.admin",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    notify => Exec['create_black_market_flag'],
  }

  exec { 'create_black_market_flag':
    cwd     => "$docroot",
    command => "echo '$black_market_flag' > ./.marketToken && chown -f www-data:www-data ./.marketToken && chmod -f 0600 ./.marketToken",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
  }
}
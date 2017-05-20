class onlinestore::install {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)

  # Parse out parameters
  $db_flag = $secgen_parameters['strings_to_leak'][0]
  $admin_flag = $secgen_parameters['strings_to_leak'][1]
  $root_file_flag = $secgen_parameters['strings_to_leak'][2]
  $black_market_flag = $secgen_parameters['strings_to_leak'][3]
  $admin_token_flag = $secgen_parameters['strings_to_leak'][4]
  $accounts = $secgen_parameters['accounts']
  $domain = $secgen_parameters['domain'][0]

  $docroot = '/var/www'
  $db_username = 'csecvm'
  $db_password = $secgen_parameters['db_password'][0]

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
    notify => Exec['add_generated_password_to_mysql_php'],
  }

  # Change the default database password to our randomly generated one
  exec { 'add_generated_password_to_mysql_php':
    cwd => $docroot,
    command => "/bin/sed -ie 's/H93AtG6akq/$db_password/g' mysql.php",
    notify => Exec['update_domain_restriction_on_email_signup'],
  }

  # Add the domain to the webapp registration restriction (signup.php and settings.php)
  exec { 'update_domain_restriction_on_email_signup':
    cwd => $docroot,
    command => "/bin/sed -ie 's/bham.ac.uk/$domain/g' signup.php",
    notify => Exec['update_domain_restriction_on_email_settings'],
  }
  exec { 'update_domain_restriction_on_email_settings':
    cwd => "$docroot/u/",
    command => "/bin/sed -ie 's/bham.ac.uk/$domain/g' settings.php",
    notify => Exec['setup_mysql'],
  }

  file { "/tmp/csecvm.sql":
    owner  => root,
    group  => root,
    mode   => '0600',
    ensure => file,
    content => template('onlinestore/csecvm.sql.erb'),
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
    notify => Exec['create_admin_token_flag'],
  }

  exec { 'create_admin_token_flag':
    cwd     => "$docroot/admin/",
    command => "echo '$admin_token_flag' > ./.adminToken && chown -f www-data:www-data ./.adminToken && chmod -f 0600 ./.adminToken",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
  }
}
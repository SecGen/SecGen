define ruby_script_container::account($username, $group, $password, $strings_to_leak, $leaked_filenames) {

  group { $group:
    ensure => present,
  }

  ::accounts::user { $username:
    shell      => '/bin/bash',
    password   => pw_hash($password, 'SHA-512', 'mysalt'),
    managehome => true,
    home_mode  => '0755',
    groups     => [$group],
    require    => Group[$group],
  }

  # strings_to_leak[0]: flag in /home/<username>/flag.txt
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "/home/$username/",
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => [$strings_to_leak[0]],
    owner             => $username,
    group             => $group,
    mode              => '2440',
    leaked_from       => "accounts_$username",
    require           => Group[$group],
  }

  file { "/home/$username/test.rb":
    owner  => $username,
    group  => $group,
    mode   => '2777',
    ensure => file,
    content => template('ruby_script_container/template.rb.erb'),
    require => Group[$group],
  }

  # exec { "$username-compileandsetup1":
  #   cwd     => "/home/$username/",
  #   command => "sudo chown $username:shadow prompt && sudo chmod 2755 prompt",
  #   path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
  # }
}
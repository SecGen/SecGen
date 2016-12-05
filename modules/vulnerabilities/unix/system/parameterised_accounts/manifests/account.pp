define parameterised_accounts::account($username, $password, $super_user, $strings_to_leak, $leaked_filename) {
  ::accounts::user { $username:
    shell      => '/bin/bash',
    password   => pw_hash($password, 'SHA-512', 'mysalt'),
    managehome => true,
  }

  # sort groups if sudo add to conf
  if $super_user {
    exec { "add-$username-to-sudoers":
      path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
      command => "echo '$username ALL=(ALL) ALL' >> /etc/sudoers",
    }
  }

  # Leak strings in a text file in the users home directory
  file { "/home/$username/$leaked_filename":
    content => template('parameterised_accounts/overshare.erb')
  }
}
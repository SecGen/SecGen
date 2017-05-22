define parameterised_accounts::account($username, $password, $super_user, $strings_to_leak, $leaked_filenames) {

  # condition because ::accounts::user changes permissions on /etc/group so needs to run before vulns/writable_groups
  if defined('writable_groups::config'){
    include ::writable_groups::config
    ::accounts::user { $username:
      shell      => '/bin/bash',
      password   => pw_hash($password, 'SHA-512', 'mysalt'),
      managehome => true,
      before     => File['/etc/group']
    }
  } else {
    ::accounts::user { $username:
      shell      => '/bin/bash',
      password   => pw_hash($password, 'SHA-512', 'mysalt'),
      managehome => true,
    }
  }

  # sort groups if sudo add to conf
  if $super_user {
    exec { "add-$username-to-sudoers":
      path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
      command => "echo '$username ALL=(ALL) ALL' >> /etc/sudoers",
    }
  }

  # Leak strings in a text file in the users home directory
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "/home/$username/",
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => $strings_to_leak,
    owner             => $username,
    leaked_from       => "accounts_$username",
  }
}
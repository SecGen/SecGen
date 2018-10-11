define parameterised_accounts::account (
  $username,
  $password,
  $super_user,
  $data_to_leak
) {
  # ::accounts::user changes permissions on group, passwd, shadow etc. so needs to run before
  if defined('writable_groups::config') {
    include ::writable_groups::config
    $writable_groups = [File['/etc/group']]
  } else { $writable_groups = [] }

  if defined('writable_passwd::config') {
    include ::writable_passwd::config
    $writable_passwd = [File['/etc/passwd']]
  } else { $writable_passwd = [] }

  if defined('writable_shadow::config') {
    include ::writable_shadow::config
    $writable_shadow = [File['/etc/shadow']]
  } else { $writable_shadow = [] }

  $misconfigurations = concat($writable_groups, $writable_passwd, $writable_shadow)

  # Add user account
  ::accounts::user { $username:
    shell      => '/bin/bash',
    password   => pw_hash($password, 'SHA-512', 'mysalt'),
    managehome => true,
    before     => $misconfigurations,
  }

  # sort groups if sudo add to conf
  if $super_user {
    exec { "add-$username-to-sudoers":
      path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
      command => "echo '$username ALL=(ALL) ALL' >> /etc/sudoers",
    }
  }

  if $password == '' {
    exec { "remove_password_from_account_$username":
      command => "/usr/bin/passwd -d $username",
      require => Accounts::User[$username],
    }
  }

  # Leak strings in a text file in the users home directory
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "/home/$username/",
    leaked_filenames  => $leaked_filenames,
    data_to_leak      => $data_to_leak,
    owner             => $username,
    leaked_from       => "accounts_$username",
  }
}
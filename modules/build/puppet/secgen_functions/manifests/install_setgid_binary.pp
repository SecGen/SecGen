# Install function for setgid binaries
# -- Modules calling this function must provide a Makefile and any .c files within it's <module_name>/files directory

define secgen_functions::install_setgid_binary (
  $challenge_name, # Challenge name, used for the wrapper-directory
  $source_module_name, # Name of the module that calls this function
  $binary_path, # Optional : Provide the path to a binary file that has already been compiled
  $group, # Name of group
  $account, # User account
  $flag, # ctf flag string
  $flag_name, # ctf flag name
  $storage_dir     = '', # Optional: Storage directory (takes precedent if supplied, e.g. nfs / smb share dir)
  $strings_to_leak = [''], # Optional: strings to leak (could contain instructions or a message)
) {

  if $account {
    $username = $account['username']

    if ! User[$username] {
      ::accounts::user { $username:
        shell      => '/bin/bash',
        password   => pw_hash($account['password'], 'SHA-512', 'mysalt'),
        managehome => true,
        home_mode  => '0755',
      }
    }

    $storage_directory = "/home/$username"

  } elsif $storage_dir {
    $storage_directory = $storage_dir

  } else {
    err('install: either account or storage_dir is required')
    fail
  }

  $challenge_directory = "$storage_directory/$challenge_name"
  $modules_source = "puppet:///modules/$source_module_name"

  if $binary_path == undef or $binary_path == ''  {
    # TODO : Unless binary path is provided ... CALL COMPILE_BINARY_MODULE!

    # TODO: Remove compile directory
    exec { "remove_$compile_directory":
      command => "/bin/rm -rf $compile_directory",
      require => [File["$challenge_directory/$challenge_name"]]
    }
  } else {

  }

  #TODO : Set the binary path. If the path has been passed in, use that.
  #TODO : Otherwise create a binary path to pass into the secgen compile_binary_module function and use that internally.
  $binary_path

  if ! Group[$group] {
    group { $group:
      ensure => present,
    }
  }

  # Create challenge directory
  ::secgen_functions::create_directory { "create_$challenge_directory":
    path => $challenge_directory,
    # notify => File["create-$compile_directory-$challenge_name"],
  }

  # Move the compiled binary into the challenge directory
  file { "$challenge_directory/$challenge_name":
    ensure  => present,
    owner   => 'root',
    group   => $group,
    mode    => '2771',
    source  => "$binary_path",
  }

  # Drop the flag file on the box and set permissions
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "$challenge_directory",
    leaked_filenames  => [$flag_name],
    strings_to_leak   => [$flag],
    owner             => 'root',
    group             => $group,
    mode              => '0440',
    leaked_from       => "accounts_$username",
    require           => [Group[$group], Exec["gcc_$challenge_name-$compile_directory"]],
    notify            => Exec["remove_$compile_directory"],
  }

}

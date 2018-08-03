# Install function for setgid binaries
# -- Modules calling this function must provide a Makefile and any .c files within it's <module_name>/files directory

define secgen_functions::install_setgid_script (
  $challenge_name, # Challenge name, used for the wrapper-directory
  $script_name, # Script filename
  $script_data, # Script data
  $source_module_name, # Name of the module that calls this function
  $group, # Name of group
  $account, # User account
  $flag, # ctf flag string
  $flag_name, # ctf flag name
  $storage_dir     = '', # Optional: Storage directory (takes precedent if supplied, e.g. nfs / smb share dir)
  $strings_to_leak = [''], # Optional: strings to leak (could contain instructions or a message)
) {

  if $account {
    $username = $account['username']

    ::accounts::user { $username:
      shell      => '/bin/bash',
      password   => pw_hash($account['password'], 'SHA-512', 'mysalt'),
      managehome => true,
      home_mode  => '0755',
    }

    $storage_directory = "/home/$username"

  } elsif $storage_dir {
    $storage_directory = $storage_dir

  } else {
    err('install: either account or storage_dir is required')
    fail
  }

  $compile_directory = "$storage_directory/tmp"
  $challenge_directory = "$storage_directory/$challenge_name"
  $modules_source = "puppet:///modules/$source_module_name"

  group { $group:
    ensure => present,
  }

  # Create challenge directory
  ::secgen_functions::create_directory { "create_$challenge_directory":
    path   => $challenge_directory,
    notify => File["$challenge_directory/$script_name"],
  }

  # Move the compiled binary into the challenge directory
  file { "$challenge_directory/$script_name":
    ensure  => present,
    owner   => 'root',
    group   => $group,
    mode    => '2775',
    content => $script_data,
    require => Group[$group],
  }

  # Drop the flag file on the box and set permissions
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "$challenge_directory",
    leaked_filenames  => [$flag_name],
    strings_to_leak   => [$flag],
    owner             => 'root',
    group             => $group,
    mode              => '0440',
    leaked_from       => "$source_module_name-$module_name",
    require           => Group[$group],
  }

}

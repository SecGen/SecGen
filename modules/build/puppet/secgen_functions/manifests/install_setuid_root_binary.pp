# Install function for setuid_root binaries
# -- Modules calling this function must provide a Makefile and any .c files within it's <module_name>/files directory

define secgen_functions::install_setuid_root_binary (
  $challenge_name,           # Challenge name, used for the wrapper-directory
  $source_module_name,      # Name of the module that calls this function
  $gcc_output_binary_name,  # Temporary name of the binary output by gcc when when /bin/make runs the Makefile
  $challenge_binary_name,   # Renamed binary on copy to challenge directory, could differ from above
  $account,                 # User account (leak here if $storage_directory is not supplied)
  $flag,                    # ctf flag string
  $storage_dir = [''],      # Optional: Storage directory (takes precedent if supplied, e.g. nfs / smb share dir)
  $strings_to_leak = [''],  # Optional: strings to leak (could contain instructions or a message)
) {

  ensure_packages('build-essential')
  ensure_packages('gcc-multilib')

  # Use either storage directory or account's home directory. storage_directory takes precedent
  if $storage_dir[0] != '' {
    $storage_directory = $storage_dir[0]
    $leaked_filenames = ["$challenge_name-instructions"]
  } elsif $account {
    $username = $account['username']
    $storage_directory = "/home/$username"
    $leaked_filenames = $account['leaked_filenames']

    ::accounts::user { $username:
      shell      => '/bin/bash',
      password   => pw_hash($account['password'], 'SHA-512', 'mysalt'),
      managehome => true,
      home_mode  => '0755',
    }
  } else {
    err('install: Either storage_directory or account is required')
    fail
  }

  $compile_directory = "$storage_directory/tmp"
  $challenge_directory = "$storage_directory/$challenge_name"
  $modules_source = "puppet:///modules/$source_module_name"

  # Create challenge directory
  file { $challenge_directory:
    ensure => directory,
  }

  # Move contents of the module's files directory into compile directory
  file { $compile_directory:
    ensure  => directory,
    recurse => true,
    source  => $modules_source,
    notify  => Exec["gcc_$gcc_output_binary_name-$compile_directory"],
  }

  # Build the binary with gcc
  exec { "gcc_$gcc_output_binary_name-$compile_directory":
    cwd     => $compile_directory,
    command => "/usr/bin/make",
    require => [File[$challenge_directory, $compile_directory], Package['build-essential', 'gcc-multilib']]
  }

  # Move the compiled binary into the challenge directory
  file { "$challenge_directory/$challenge_binary_name":
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '4755',
    source  => "$compile_directory/$gcc_output_binary_name",
    require => Exec["gcc_$gcc_output_binary_name-$compile_directory"],
  }

  # Drop the flag file on the box and set permissions
  file { "$challenge_directory/flag":
    ensure   => present,
    content => $flag,
    mode     => '0600',
    require  => Exec["gcc_$gcc_output_binary_name-$compile_directory"],
  }

  # Remove compile directory
  exec { "remove_$compile_directory":
    command => "/bin/rm -rf $compile_directory",
    require => File["$challenge_directory/$challenge_binary_name", "$challenge_directory/flag"]
  }

  # Leak messages / instructions in a text file in the storage directory / home directory
  ::secgen_functions::leak_files { "$challenge_directory-strings_to_leak":
    storage_directory => $challenge_directory,
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => $strings_to_leak,
    leaked_from       => $source_module_name,
  }
}

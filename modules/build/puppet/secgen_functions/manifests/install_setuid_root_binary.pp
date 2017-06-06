# Install function for setuid_root binaries
# -- Modules calling this function must provide a Makefile and any .c files within it's <module_name>/files directory

define secgen_functions::install_setuid_root_binary (
  $source_module_name,      # Name of the module that calls this function
  $gcc_output_binary_name,  # Temporary name of the binary output by gcc when when /bin/make runs the Makefile
  $challenge_binary_name,   # Renamed binary on copy to challenge directory, could differ from above
  $storage_directory,       # Storage directory
  $flag,                    # ctf flag string
) {

  $compile_directory = "$storage_directory/tmp"
  $modules_source = "puppet:///modules/$source_module_name"

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
  }

  # Move the compiled binary into the storage directory
  file { "$storage_directory/$challenge_binary_name":
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '4755',
    source  => "$compile_directory/$gcc_output_binary_name",
    require => Exec["gcc_$gcc_output_binary_name-$compile_directory"],
  }

  # Drop the flag file on the box and set permissions
  file { "$storage_directory/flag":
    ensure   => present,
    content => $flag,
    mode     => '0600',
    require  => Exec["gcc_$gcc_output_binary_name-$compile_directory"],
  }

  # Remove compile directory
  exec { "remove_$compile_directory":
    command => "/bin/rm -rf $compile_directory",
    require => File["$storage_directory/$challenge_binary_name", "$storage_directory/flag"]
  }
}

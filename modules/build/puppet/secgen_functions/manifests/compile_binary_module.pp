# Install function for setgid binaries
# -- Modules calling this function must provide a Makefile and any .c files within it's <module_name>/files directory

define secgen_functions::compile_binary_module (
  $source_module_name, # Name of the module that calls this function
) {

  $modules_source = "puppet:///modules/$source_module_name"
  $compile_directory = "/tmp/"

  # Move contents of the module's files directory into compile directory
  file { "create-$compile_directory-$source_module_name":
    path => $compile_directory,
    ensure  => directory,
    recurse => true,
    source  => $modules_source,
  }

  # Build the binary with gcc
  exec { "gcc_$challenge_name-$compile_directory":
    cwd     => $compile_directory,
    command => "/usr/bin/make",
    require => File["create-$compile_directory-$challenge_name"]
  }
}

class dc16_amadhj::install {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_params = parsejson($json_inputs)

  $account = parsejson($secgen_params['account'][0])
  $username = $account['username']
  $flag = $secgen_params['flag'][0]
  $binary_name = $secgen_params['binary_name'][0]

  $base_directory = "/home/$username"
  $compile_directory = "$base_directory/tmp"
  $modules_source = "puppet:///modules/$module_name"

  # Move contents of the module's files directory into compile directory
  file { $compile_directory:
    ensure  => directory,
    recurse => true,
    source => $modules_source,
    notify => Exec['gcc_$binary_name_$compile_directory'],
  }

  # Build the binary with gcc
  exec { 'gcc_$binary_name_$compile_directory':
    cwd => $compile_directory,
    command => "/usr/bin/make",
  }

  # Move the compiled binary into the base_directory
  file { "$base_directory/$binary_name":
    ensure => present,
    owner => 'root',
    group => 'root',
    mode => '4755',
    source => "$compile_directory/$binary_name",
    require => Exec['gcc_$binary_name_$compile_directory'],
  }

  # Drop the flag file on the box and set permissions
  file { "$base_directory/flag":
    ensure => present,
    content => $flag,
    mode => '0600',
    require => Exec['gcc_$binary_name_$compile_directory'],
  }

  # Remove compile directory
  exec { "remove_$compile_directory":
    command => "/bin/rm -rf $compile_directory",
  }

}

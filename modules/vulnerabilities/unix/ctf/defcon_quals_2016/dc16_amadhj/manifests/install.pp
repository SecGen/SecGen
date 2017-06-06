class dc16_amadhj::install {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_params = parsejson($json_inputs)
  $account = parsejson($secgen_params['account'][0])
  $username = $account['username']
  $leaked_filenames = $account['leaked_filenames']
  $strings_to_leak = $secgen_params['strings_to_leak']

  # Use either storage directory or account's home directory. storage_directory takes precedent
  if $secgen_params['storage_directory'] {
    $storage_directory = $secgen_params['storage_directory'][0]
  } elsif $account {
    $storage_directory = "/home/$username"

    ::accounts::user { $username:
      shell      => '/bin/bash',
      password   => pw_hash($account['password'], 'SHA-512', 'mysalt'),
      managehome => true,
      home_mode  => '0755',
    }
  } else {
    err('dc16_amadhj::install: No storage_directory or account provided')
    fail
  }

  ::secgen_functions::install_setuid_root_binary { 'defcon16_amadhj':
    source_module_name     => $module_name,
    gcc_output_binary_name => 'amadhj',
    challenge_binary_name  => $secgen_params['binary_name'][0],
    storage_directory      => $storage_directory,
    flag                   => $secgen_params['flag'][0],
  }

  # Leak strings in a text file in the storage directory / home directory
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => $storage_directory,
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => $strings_to_leak,
    owner             => $username,
    leaked_from       => "accounts_$username",
  }

}

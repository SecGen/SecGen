define secgen_functions::leak_file($leaked_filename, $storage_directory, $strings_to_leak, $owner = 'root', $group = 'root', $mode = '0777', $leaked_from = '' ) {
  $path_to_leak = "$storage_directory/$leaked_filename"

  # If the file already exists append to it, otherwise create it.
  if (defined(File[$path_to_leak])){
    notice("File with that name already defined, appending leaked strings instead...")
    exec { "$leaked_from-$path_to_leak":
      path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'],
      command => "echo $strings_to_leak >> $path_to_leak",
    }
  } else {
    file { $path_to_leak:
      ensure   => present,
      owner    => $owner,
      group    => $group,
      mode     => $mode,
      content  => template('secgen_functions/overshare.erb')
    }
  }
}

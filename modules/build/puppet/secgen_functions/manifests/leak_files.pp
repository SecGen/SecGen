define secgen_functions::leak_files($leaked_filenames, $storage_directory, $strings_to_leak, $owner = 'root', $group = 'root', $mode = '0777', $leaked_from) {

  # $leaked_from is a mandatory resource specifying where the file was being leaked (i.e. which module / user leaked it.)
  # This is to avoid resource clashes if two users get the same 'leaked_filenames' results
  $leak_pairs = zip($strings_to_leak, $leaked_filenames)
  $leak_pairs.each |$counter, $leak_pair| {
      $leaked_strings = $leak_pair[0]
      $leaked_filename = $leak_pair[1]

      # until we run out of filenames, create a new file per string
      unless $leaked_filename == undef {
        $leaked_file_resource = "$leaked_from-$leaked_filename-$counter"
        secgen_functions::leak_file { $leaked_file_resource:
          leaked_filename          => $leaked_filename,
          storage_directory        => $storage_directory,
          strings_to_leak          => $leaked_strings,
          owner                    => $owner,
          mode                     => $mode,
        }
      } else {
        # Then just add to first file.
        $first_filename = $leaked_filenames[0]
        $leaked_file_resource = "$leaked_from-$first_filename-$counter"
        secgen_functions::leak_file { $leaked_file_resource:
          leaked_filename          => $first_filename,
          storage_directory        => $storage_directory,
          strings_to_leak          => $leaked_strings,
          owner                    => $owner,
          mode                     => $mode,
          leaked_from              => $leaked_file_resource,  # pass this in when appending to avoid resource clashes
        }
      }
    }
}

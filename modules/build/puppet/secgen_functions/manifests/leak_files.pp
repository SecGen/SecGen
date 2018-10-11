define secgen_functions::leak_files($leaked_filenames=[], $storage_directory, $strings_to_leak=[], $data_to_leak=[], $images_to_leak=[], $owner = 'root', $group = 'root', $mode = '0660', $leaked_from) {

  # Have a check on $data_to_leak for whether the file is a string or json with {"secgen_leaked_data": {}}
  $data_to_leak.each |$i, $data_element| {
    notice ('Looping through $data_to_leak ... ')
    if "secgen_leaked_data" in $data_element {
      $secgen_leaked_data = parsejson($data_element)
      notice ("[$i] leaking secgen_leaked_data {} ... ")

      $data = $secgen_leaked_data['secgen_leaked_data']['data']
      $filename = $secgen_leaked_data['secgen_leaked_data']['filename']
      $ext = $secgen_leaked_data['secgen_leaked_data']['ext']
      $subdirectory = $secgen_leaked_data['secgen_leaked_data']['subdirectory']

      $path_to_leak = "$storage_directory/$subdirectory/$filename.$ext"
      $leaked_file_resource = "$leaked_from-$path_to_leak"

      file { $path_to_leak:
        ensure  => present,
        owner   => $owner,
        group   => $group,
        mode    => $mode,
        content => base64('decode', $data)
      }
    } else {
      notice("[$i] leaking string ... ")

      # $filename = $leaked_filenames[0]
      # $path_to_leak = "$storage_directory/$filename.txt"
      #
      # file { $path_to_leak:
      #   ensure   => present,
      #   owner    => $owner,
      #   group    => $group,
      #   mode     => $mode,
      #   content  => $data_element
      # }
    }
  }

  # $leaked_from is a mandatory resource specifying where the file was being leaked (i.e. which module / user leaked it.)
  # This is to avoid resource clashes if two users get the same 'leaked_filenames' results

  # Pair strings with the leaked_filenames and leak them.
  # $string_leak_pairs = zip($strings_to_leak, $leaked_filenames)
  # $string_leak_pairs.each |$counter, $leak_pair| {
  #     $leaked_strings = $leak_pair[0]
  #     $leaked_filename = $leak_pair[1]
  #
  #     # until we run out of filenames, create a new file per string
  #     unless $leaked_filename == undef {
  #       $leaked_file_resource = "$leaked_from-$leaked_filename-$counter"
  #       secgen_functions::leak_file { $leaked_file_resource:
  #         leaked_filename          => $leaked_filename,
  #         storage_directory        => $storage_directory,
  #         strings_to_leak          => $leaked_strings,
  #         owner                    => $owner,
  #         mode                     => $mode,
  #         group                    => $group,
  #       }
  #     } else {
  #       # Then just add to first file.
  #       $first_filename = $leaked_filenames[0]
  #       $leaked_file_resource = "$leaked_from-$first_filename-$counter"
  #       secgen_functions::leak_file { $leaked_file_resource:
  #         leaked_filename          => $first_filename,
  #         storage_directory        => $storage_directory,
  #         strings_to_leak          => $leaked_strings,
  #         owner                    => $owner,
  #         mode                     => $mode,
  #         group                    => $group,
  #         leaked_from              => $leaked_file_resource,  # pass this in when appending to avoid resource clashes
  #       }
  #     }
  #   }
  # #
  # # # Leak images with name image#{$counter}.png
  # # # First file is image1.png not image0.png
  # # $images_to_leak.each |$counter, $image_contents| {
  # #   $num = $counter + 1
  # #   $filename = "image$num.png"
  # #   $path_to_leak = "$storage_directory/$filename"
  # #   $leaked_file_resource = "$leaked_from-$filename"
  # #
  # #   file { $path_to_leak:
  # #     ensure  => present,
  # #     owner   => $owner,
  # #     group   => $group,
  # #     mode    => $mode,
  # #     content => base64('decode', $image_contents)
  # #   }
  # }
}

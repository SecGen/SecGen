class metactf::configure {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)
  $install_dir = '/opt/metactf'
  $challenge_list = $secgen_params['challenge_list']
  $flags = $secgen_params['flags']
  $groups = $secgen_params['groups']

  $raw_account = $secgen_params['account'][0]
  $account = parsejson($raw_account)
  $username = $account['username']

  # TODO : Test me with dynamic challenge directory...
  # if $secgen_params['challenge_directory'][0] != undef {
  #   $challenge_directory = $secgen_params['challenge_directory'][0]
  # } else {
  $storage_dir = "/home/$username/challenges"
  # }

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  # Move the challenges based on account name and challenge name.

  $challenge_pairs = zip($challenge_list, $flags)

  $challenge_pairs.each |$counter, $challenge_pair| {
    $challenge_path = $challenge_pair[0]
    $flag = $challenge_pair[1]
    $split_challenge = split($challenge_path, '/')
    $metactf_challenge_dir = $split_challenge[0]
    $metactf_challenge_type = split($metactf_challenge_dir, '_')[1]
    $challenge_name = $split_challenge[1]
    $group = $groups[$counter]

    $binary_path = "$install_dir/$metactf_challenge_dir/obj/secgen/$metactf_challenge_type/$challenge_name"

    ::secgen_functions::install_setgid_binary { "metactf_$challenge_name":
      source_module_name => $module_name,
      challenge_name     => $challenge_name,
      group              => $group,
      account            => $account,
      flag               => $flag,
      flag_name          => 'flag',
      binary_path        => $binary_path,
      storage_dir        => $storage_dir,
      strings_to_leak    => $secgen_params['strings_to_leak'],
    }
  }

}
class metactf::install {
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

  file { $install_dir:
    ensure  => directory,
    recurse => true,
    source  => 'puppet:///modules/metactf/repository',
  }

  exec { 'set install.sh mode':
    command => "chmod +x $install_dir/install.sh",
  }

  exec { 'install metactf dependencies':
    command => "/bin/bash $install_dir/install.sh"
  }

  # For now just build all of the binaries.
  exec { 'build ctf_angr binaries':
    command => "/bin/make /opt/metactf/src_angr/"
  }

  # Move the challenges based on account name and challenge name.

  $challenge_pairs = zip($challenge_list, $flags)

  $challenge_pairs.each |$counter, $challenge_pair| {
    $challenge_path = $challenge_pair[0]
    $flag = $challenge_pair[1]
    $split_challenge = split($challenge_path, '/')
    $metactf_challenge_type = $split_challenge[0]
    $challenge_name = $split_challenge[1]
    $group = $groups[$counter]

    if $group {
      ::secgen_functions::install_setgid_binary { "metactf_$challenge_name":
        source_module_name => $module_name,
        challenge_name     => $challenge_name,
        group              => $group,
        account            => $account,
        flag               => $flag,
        flag_name          => 'flag',
        storage_dir        => $storage_dir,
        strings_to_leak    => $secgen_params['strings_to_leak'],
      }
    } else {  # TODO : Refactor so that this works well with a default account ? (should we make it so that if we just include metactf it will throw out 1 random challenge with a default account or just not bother?)
      ::secgen_functions::install_setuid_root_binary { "metactf_$challenge_name":
        source_module_name => $module_name,
        challenge_name     => $secgen_params['challenge_name'][0],
        account            => $account,
        flag               => $secgen_params['flag'][0],
        flag_name          => 'flag',
        storage_dir        => $storage_dir,
        strings_to_leak    => $secgen_params['strings_to_leak'],
      }
    }
  }

}
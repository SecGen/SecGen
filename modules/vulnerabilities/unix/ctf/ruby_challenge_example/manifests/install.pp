class ruby_challenge_example::install {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)
  $challenge_name = $secgen_params['challenge_name'][0]
  $script_data = $secgen_params['script_data']

  # TODO: Do we move the if populated checks (below) inside the install function? Might be worthwhile.
  # TODO: It would result in reduced boilerplate for script / binary challenge install modules.

  if $secgen_params['group'] and $secgen_params['group'][0]{
    $group = $secgen_params['group'][0]
  } else {
    $group = $challenge_name
  }

  if $secgen_params['account'][0] and $secgen_params['account'][0] != '' {
    $account = parsejson($secgen_params['account'][0])
  } else {
    $account = undef
  }

  if $secgen_params['storage_directory'] and $secgen_params['storage_directory'][0] {
    $storage_dir = $secgen_params['storage_directory'][0]
  } else {
    $storage_dir = undef
  }

  if $secgen_params['port'] and $secgen_params['port'][0] {
    $port = $secgen_params['port'][0]
    notice("$module_name - running on port: $port")
  } else {
    $port = undef
  }

  ::secgen_functions::install_setgid_script { 'ruby_challenge_example':
    source_module_name => $module_name,
    challenge_name     => $challenge_name,
    script_name        => 'test.rb',
    script_data        => $script_data[0],
    group              => $group,
    account            => $account,
    flag               => $secgen_params['flag'][0],
    port               => $port,
    storage_dir        => $storage_dir,
    strings_to_leak    => $secgen_params['strings_to_leak'],
  }
}

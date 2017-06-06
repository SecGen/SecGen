class dc16_amadhj::install {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_params = parsejson($json_inputs)
  $account = parsejson($secgen_params['account'][0])

  ::secgen_functions::install_setuid_root_binary { 'defcon16_amadhj':
    source_module_name     => $module_name,
    challenge_name         => $secgen_params['challenge_name'][0],
    gcc_output_binary_name => 'amadhj',
    challenge_binary_name  => $secgen_params['binary_name'][0],
    account                => $account,
    flag                   => $secgen_params['flag'][0],
    storage_dir            => $secgen_params['storage_directory'],
    strings_to_leak        => $secgen_params['strings_to_leak'],
  }
}

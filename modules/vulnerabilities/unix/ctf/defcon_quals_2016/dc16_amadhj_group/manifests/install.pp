class dc16_amadhj_group::install {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)

  if $secgen_params['account'][0] != ''{
    $account = parsejson($secgen_params['account'][0])
  } else {
    $account = undef
  }
  notice("account: ")
  notice($account)

  ::secgen_functions::install_setgid_binary { 'defcon16_amadhj_group':
    source_module_name     => 'dc16_amadhj_group',
    challenge_name         => $secgen_params['challenge_name'][0],
    group                  => $secgen_params['group'][0],
    account                => $account,
    flag                   => $secgen_params['flag'][0],
    flag_name              => 'flag',
    storage_dir            => $secgen_params['storage_directory'][0],
    strings_to_leak        => $secgen_params['strings_to_leak'],
  }
}

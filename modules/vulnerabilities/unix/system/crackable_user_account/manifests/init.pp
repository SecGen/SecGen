class crackable_user_account::init {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)

  $account = parsejson($secgen_parameters['accounts'][0])
  $username = $account['username']

  ::parameterised_accounts::account { "crackable_user_account_$username":
    username        => $username,
    password        => $account['password'],
    super_user      => str2bool($account['super_user']),
    strings_to_leak => $secgen_parameters['strings_to_leak'],
    leaked_filenames => $account['leaked_filenames']
  }
}
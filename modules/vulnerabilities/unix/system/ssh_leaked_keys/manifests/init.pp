class ssh_leaked_keys::init {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)

  $accounts = $secgen_parameters['accounts']
  $accounts.each |$raw_account| {
      $account = parsejson($raw_account)
      $username = $account['username']
      ssh_leaked_keys::account { "ssh_leaked_keys_$username":
        username         => $username,
        password         => $account['password'],
        strings_to_leak  => $account['strings_to_leak'],
        leaked_filenames => $account['leaked_filenames']
      }
    }
}
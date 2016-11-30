class parameterised_accounts::init {
  $secgen_parameters = parsejson($::json_inputs)

  $accounts = $secgen_parameters['accounts']
  $accounts.each |$account| {
      parameterised_accounts::account { "parameterised-$account-name":
        username        => $account['username'],
        password        => $account['password'],
        super_user      => str2bool($account['super_user']),
        strings_to_leak => $account['strings_to_leak'],
        leaked_filename => $account['leaked_filename']
      }
    }
}
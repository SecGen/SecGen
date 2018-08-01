class ruby_script_container::init {
  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $group = $secgen_parameters['group'][0]

  ::accounts::user { 'temp':
    shell      => '/bin/bash',
    password   => pw_hash('temp', 'SHA-512', 'mysalt'),
    managehome => true,
    home_mode  => '0755',
    groups => [$group],
  }

  $accounts = $secgen_parameters['accounts']
  $accounts.each |$raw_account| {
    $account  = parsejson($raw_account)
    $username = $account['username']
    ruby_script_container::account { "script_container_$username":
      username         => $username,
      password         => $account['password'],
      group            => $group,
      strings_to_leak  => $account['strings_to_leak'],
      leaked_filenames => $account['leaked_filenames']
    }
  }
}
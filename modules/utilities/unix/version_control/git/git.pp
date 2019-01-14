$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$user = parsejson($secgen_parameters['user'][0])

include git

git::config {'user.name':
  value => $user['name']
}

git::config { 'user.email':
  value => $user['email_address']
}
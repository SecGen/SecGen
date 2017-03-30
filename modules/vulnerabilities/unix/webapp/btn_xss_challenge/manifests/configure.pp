class btn_xss_challenge::configure {
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)


  # Create www-data user in mysql
  ::mysql::db { 'mydb':
    user     => 'www-data',
    password => 'example',
    host     => 'localhost',
    grant    => ['SELECT', 'UPDATE'],
  }
}
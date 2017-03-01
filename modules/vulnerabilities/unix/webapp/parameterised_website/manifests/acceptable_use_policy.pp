class parameterised_website::acceptable_use_policy {
  # Pull SecGen Parameters through
  $json_inputs = base64('decode', $::base64_inputs)
  $secgen_parameters = parsejson($json_inputs)
  $acceptable_use_policy = str2bool($secgen_parameters['host_acceptable_use_policy'][0])

  if $acceptable_use_policy {
    $business_name = $secgen_parameters['business_name'][0]
    $home = '/var/www'

    Exec { path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'], }

    # Apply template
    file{ "$home/acceptable_use_policy.html":
      ensure  => file,
      content => template('parameterised_website/acceptable_use_page.html.erb')
    }
  }
}
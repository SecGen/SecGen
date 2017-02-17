class parameterised_website::security_audit_remit {
  # Pull SecGen Parameters through
  $secgen_parameters = parsejson($::json_inputs)
  $security_audit = $secgen_parameters['security_audit']

  if $security_audit {
    $security_audit_remit = $security_audit[0]
    $business_name = $secgen_parameters['business_name'][0]
    $home = '/var/www'

    Exec { path    => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'], }

    # Apply html data
    file{ "$home/security_audit_remit.html":
      ensure  => file,
      content => template('parameterised_website/security_audit_remit_page.html.erb'),
    }
  }
}
class parameterised_website::install {
  $secgen_parameters = parsejson($::json_inputs)
  $acceptable_use_policy = str2bool($secgen_parameters['host_acceptable_use_policy'][0])
  $docroot = '/var/www'

  if $acceptable_use_policy {  # Use alternative intranet index.html template
    $index_template = 'parameterised_website/intranet_index.html.erb'
  } else {
    $index_template = 'parameterised_website/index.html.erb'
  }

  # Move boostrap css+js over
  file { "$docroot/css":
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/parameterised_website/css',
  }
  file { "$docroot/js":
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/parameterised_website/js',
  }

  # Apply default CSS template
  file { "$docroot/css/default.css":
  ensure => file,
  content => template('parameterised_website/default.css.erb')
  }

  # Apply index page template
  file { "$docroot/index.html":
    ensure  => file,
    content => template($index_template),
  }

  # Apply contact page template
  file { "$docroot/contact.html":
    ensure  => file,
    content => template('parameterised_website/contact.html.erb'),
  }
}
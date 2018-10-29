class metactf::install {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)

  file { '/usr/lib/metactf':
    ensure => directory,
    recurse => true,
    source => 'puppet:///modules/metactf/repository',
  }

}
class labtainers::config{
  require labtainers::install

  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $lab = $secgen_parameters['lab'][0]

  exec { 'start lab':
    command  => "/opt/labtainers/labtainer-student/labtainer $lab",
    provider => shell,
  }

}

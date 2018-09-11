$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$ip_address = $secgen_parameters['IP_address'][0]  # TODO: Which IP address? how do we do this with two servers?

include ::java

class { 'elasticsearch': }
elasticsearch::instance { 'es-01': }


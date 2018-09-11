$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$ip_address = $secgen_parameters['IP_address'][0]  # TODO: Which IP address? how do we do this with two servers?

class { 'kibana':
  config => {
    'server.host'       => $ip_address,
    'elasticsearch.url' => "http://$ip_address:9200",
    'server.port'       => '5601',
  }
}


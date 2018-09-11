class filebeat::setup {

  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $ip_address = $secgen_parameters['IP_address'][0]  # TODO: Which IP address? how do we do this with two servers?


  class { 'filebeat':
    outputs => {
      'logstash' => {
        'hosts' => [
          "$ip_address:5043",
        ],
        'index' => 'filebeat',
      },
    },
  }

  filebeat::prospector { 'syslogs':
    paths    => [
      '/var/log/auth.log',
      '/var/log/syslog',
    ],
    doc_type => 'syslog-beat',
  }
}
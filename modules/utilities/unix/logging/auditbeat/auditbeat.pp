$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$logstash_ip = $secgen_parameters['logstash_ip'][0]
$logstash_port = 0 + $secgen_parameters['logstash_port'][0]

class { 'auditbeat':
  modules => [
    {
      'module'  => 'file_integrity',
      'enabled' => true,
      'paths'   => ['/bin', '/usr/bin', '/sbin', '/usr/sbin', '/etc'],
    },
    # {
    #   'module'  => 'auditd',
    #   'enabled' => true,
    #   '' => [''],  TODO: this needs correctly configuring. see https://www.elastic.co/guide/en/beats/auditbeat/current/auditbeat-module-auditd.html
    # },
  ],
  outputs => {
    'logstash' => {
      'hosts' => ["http://$logstash_ip:$logstash_port"],
      'index' => 'auditbeat-%{+YYYY.MM.dd}',
    },
  },
}
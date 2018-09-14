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
      # 'module'  => 'auditd',
      # 'enabled' => true,
      # 'audit_rules' => '-a always,exit -F arch=b64 -S all -F key=64bit-abi',
       # TODO: this needs correctly configuring. see https://www.elastic.co/guide/en/beats/auditbeat/current/auditbeat-module-auditd.html
    # },
  ],
  outputs => {
    'logstash' => {
      'hosts' => ["$logstash_ip:$logstash_port"],
    },
  },
}


#
# class { 'auditbeat':
#   modules => [
#     {
#       'module'  => 'file_integrity',
#       'enabled' => true,
#       'paths'   => ['/bin', '/usr/bin', '/sbin', '/usr/sbin', '/etc'],
#     },
#     {
#       'module'      => 'auditd',
#       'enabled'     => true,
#     },
#   ],
#   outputs => {
#     'elasticsearch' => {
#       'hosts' => ['http://localhost:9200'],
#       'index' => 'auditbeat-%{+YYYY.MM.dd}',
#     },
#   }
# }
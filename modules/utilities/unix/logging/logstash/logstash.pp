$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$ip_address = $secgen_parameters['ip_address'][0]  # TODO: Which IP address? how do we do this with two servers?

class { 'logstash':
  settings => {
    'http.host' => $ip_address,
  }
}

logstash::plugin { 'logstash-input-beats': }

#  TODO : Find out what this does. It's likely a .conf file template, not sure why hes using this @(""/L) | "" instead of an erb
#  TODO : ... unless it's just for the sake of the demonstration.

#   $myconfig =  @("MYCONFIG"/L)
# input {
#   beats {
#     port => 5043
#   }
# }
# output {
#   elasticsearch {
#     hosts => "192.168.1.133:9200"
#     manage_template => false
#     index => "%{[@metadata][beat]}-%{+YYYY.MM.dd}"
#     document_type => "%{[@metadata][type]}"
#   }
#   stdout { codec => rubydebug }
# }
# | MYCONFIG
#
# logstash::configfile { '02-beats-input.conf':
#   content => $myconfig,
# }
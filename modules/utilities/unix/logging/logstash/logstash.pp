$secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
$logstash_port = 0 + $secgen_parameters['logstash_port'][0]
$elasticsearch_ip = $secgen_parameters['elasticsearch_ip'][0]
$elasticsearch_port = 0 + $secgen_parameters['elasticsearch_port'][0]

include logstash

# You must provide a valid pipeline configuration for the service to start.
logstash::configfile { 'my_ls_config':
  content => template('logstash/configfile-template.erb'),
}


# TODO: Delete this if its a problem
#
# class { 'logstash':
#   settings => {
#     'http.host' => $ip_address,
#   }
# }

# logstash::plugin { 'logstash-input-beats': }


#   $myconfig =  @("MYCONFIG"/L)
# input {
#   beats {
#     port => 5044
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
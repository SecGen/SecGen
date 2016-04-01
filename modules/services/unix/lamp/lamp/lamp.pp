include lamp

#probably dont need this additional section but PHP wont work without it.
exec { 'add_sql':
  command => 'apt-get --yes --force-yes install php5-mysql',
  provider => 'shell'
  # path    => [ '/usr/local/bin/', '/bin/' ],  # alternative syntax
}

# = Define: apache::listen
#
# This define creates a Listen statement in Apache configuration
# It adds a single configuration file to Apache conf.d with the Listen
# statement
#
# == Parameters
#
# [*namevirtualhost*]
#   If to add a NameVirtualHost for this port. Default: *
#   (it creates a NameVirtualHost <%= @namevirtualhost %>:<%= @port %> entry)
#   Set to false to listen to the port without a NameVirtualHost
#
# == Examples
# apache::listen { '8080':}
#
define apache::listen (
  $namevirtualhost = '*',
  $ensure          = 'present',
  $template        = 'apache/listen.conf.erb',
  $notify_service  = true ) {

  include apache

  $manage_service_autorestart = $notify_service ? {
    true    => 'Service[apache]',
    false   => undef,
  }

  file { "Apache_Listen_${name}.conf":
    ensure  => $ensure,
    path    => "${apache::config_dir}/conf.d/0000_listen_${name}.conf",
    mode    => $apache::config_file_mode,
    owner   => $apache::config_file_owner,
    group   => $apache::config_file_group,
    require => Package['apache'],
    notify  => $manage_service_autorestart,
    content => template($template),
    audit   => $apache::manage_audit,
  }

}

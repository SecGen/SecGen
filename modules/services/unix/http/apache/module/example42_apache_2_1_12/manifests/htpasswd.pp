# = Define apache::htpasswd
#
# This define managed apache htpasswd files
# Based on CamptoCamp Apache module:
# https://github.com/camptocamp/puppet-apache/blob/master/manifests/auth/htpasswd.pp
#
# == Parameters
#
# [*ensure*]
#   Define if the add (present) or remove the user (set as $name)
#   Default: 'present',
#
# [*htpasswd_file*]
#   Path of the htpasswd file to manage.
#   Default: "${apache::params::config_dir}/htpasswd"
#
# [*username*]
#   Define username when you want to put the username in different files 
#   Default: $name
#
# [*crypt_password*]
#   Crypted password (as it appears in htpasswd)
#   Default: false (either crypt_password or clear_password must be set)
#
# [*clear_password*]
#   Clear password (as it appears in htpasswd)
#   Default: false (either crypt_password or clear_password must be set)
#
#
# == Usage
#
# Set clear password='mypass' to user 'my_user' on default htpasswd file:
# apache::htpasswd { 'myuser':
#   clear_password => 'my_pass',
# }
#
# Set crypted password to user 'my_user' on custom htpasswd file:
# apache::htpasswd { 'myuser':
#   crypt_password => 'B5dPQYYjf.jjA',
#   htpasswd_file  => '/etc/httpd/users.passwd',
# }
#
# Set the same user in different files 
# apache::htpasswd { 'myuser':
#   crypt_password => 'password1',
#   htpasswd_file  => '/etc/httpd/users.passwd'
# }
#
# apache::htpasswd { 'myuser2':
#   crypt_password => 'password2',
#   username       => 'myuser',
#   htpasswd_file  => '/etc/httpd/httpd.passwd'
# }
#
define apache::htpasswd (
  $ensure           = 'present',
  $htpasswd_file    = '',
  $username         = $name,
  $crypt_password   = false,
  $clear_password   = false ) {

  include apache

  $real_htpasswd_file = $htpasswd_file ? {
    ''      => "${apache::params::config_dir}/htpasswd",
    default => $htpasswd_file,
  }

  case $ensure {

    'present': {
      if $crypt_password and $clear_password {
        fail 'Choose only one of crypt_password OR clear_password !'
      }

      if !$crypt_password and !$clear_password  {
        fail 'Choose one of crypt_password OR clear_password !'
      }

      if $crypt_password {
        exec { "test -f ${real_htpasswd_file} || OPT='-c'; htpasswd -b \${OPT} ${real_htpasswd_file} ${username} '${crypt_password}'":
          unless => "grep -q '${username}:${crypt_password}' ${real_htpasswd_file}",
          path   => '/bin:/sbin:/usr/bin:/usr/sbin',
        }
      }

      if $clear_password {
        exec { "test -f ${real_htpasswd_file} || OPT='-c'; htpasswd -bp \$OPT ${real_htpasswd_file} ${username} ${clear_password}":
          unless => "egrep '^${username}:' ${real_htpasswd_file} && grep ${username}:\$(mkpasswd -S \$(egrep '^${username}:' ${real_htpasswd_file} |cut -d : -f 2 |cut -c-2) ${clear_password}) ${real_htpasswd_file}",
          path   => '/bin:/sbin:/usr/bin:/usr/sbin',
        }
      }
    }

    'absent': {
      exec { "htpasswd -D ${real_htpasswd_file} ${username}":
        onlyif => "egrep -q '^${username}:' ${real_htpasswd_file}",
        notify => Exec["delete ${real_htpasswd_file} after remove ${username}"],
        path   => '/bin:/sbin:/usr/bin:/usr/sbin',
      }

      exec { "delete ${real_htpasswd_file} after remove ${username}":
        command     => "rm -f ${real_htpasswd_file}",
        onlyif      => "wc -l ${real_htpasswd_file} | egrep -q '^0[^0-9]'",
        refreshonly => true,
        path        => '/bin:/sbin:/usr/bin:/usr/sbin',
      }
    }

    default: { }
  }
}

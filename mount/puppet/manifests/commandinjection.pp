 class { 'apache': mpm_module => 'prefork'  }
    apache::vhost { 'localhost':
      port    => '80',
      docroot => '/var/www/commandinjection',
    }   
include apache::mod::php
package { ['php5', 'libapache2-mod-php5']:
      ensure => installed,
      notify => Service["apache2"]
  }

file { "/var/www/commandinjection":
    ensure => directory,
    recurse => true,
    source => "/mount/files/web/commandinjection/"
}

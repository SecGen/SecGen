# Puppet module: apache

This is a Puppet apache module from the second generation of Example42 Puppet Modules.

Made by Alessandro Franceschi / Lab42

Official site: http://www.example42.com

Official git repository: http://github.com/example42/puppet-apache

Released under the terms of Apache 2 License.

This module requires functions provided by the Example42 Puppi module.

For detailed info about the logic and usage patterns of Example42 modules read README.usage on Example42 main modules set.

## USAGE - Module specific usage

* Install apache with a custom httpd.conf template and some virtual hosts

         class { 'apache':
           template => 'example42/apache/httpd.conf.erb',
         }

         apache::vhost { 'mysite':
           docroot  => '/path/to/docroot',
           template => 'example42/apache/vhost/mysite.com.erb',
         }


* Install mod ssl

        include apache::ssl


* Manage basic auth users (Here user joe is created with the $crypt_password on the defined htpasswd_file

        apache::htpasswd { 'joe':
          crypt_password => 'B5dPQYYjf.jjA',
          htpasswd_file  => '/etc/httpd/users.passwd',
        }


* Manage custom configuration files (created in conf.d, source or content can be defined)

        apache::dotconf { 'trac':
          content => template("site/trac/apache.conf.erb")
        }


* Add other listening ports (a relevant NameVirtualHost directive is automatically created)

        apache::listen { '8080': }


* Add other listening ports without creating a relevant NameVirtualHost directive

        apache::listen { '8080':
          $namevirtualhost = false,
        }


* Add an apache module and manage its configuraton

        apache::module { 'proxy':
          templatefile => 'site/apache/module/proxy.conf.erb',
        }


* Install mod passenger

        include apache::passenger


## USAGE - Basic management

* Install apache with default settings

        class { "apache": }

* Disable apache service.

        class { "apache":
          disable => true
        }

* Disable apache service at boot time, but don't stop if is running.

        class { "apache":
          disableboot => true
        }

* Remove apache package

        class { "apache":
          absent => true
        }

* Enable auditing without making changes on existing apache configuration files

        class { "apache":
          audit_only => true
        }

* Install apache with a specific version

        class { "apache":
          version =>  '2.2.22'
        }


## USAGE - Default server management

* Simple way to manage default apache configuration

        apache::vhost { 'default':
            docroot             => '/var/www/document_root',
            server_name         => false,
            priority            => '',
            template            => 'apache/virtualhost/vhost.conf.erb',
        }

* Using a source file to create the vhost

        apache::vhost { 'default':
	        source 		=> 'puppet:///files/web/default.conf',
	        template	=> '',
        }


## USAGE - Overrides and Customizations

* Use custom sources for main config file

        class { "apache":
          source => [ "puppet:///modules/lab42/apache/apache.conf-${hostname}" , "puppet:///modules/lab42/apache/apache.conf" ],
        }


* Use custom source directory for the whole configuration dir

        class { "apache":
          source_dir       => "puppet:///modules/lab42/apache/conf/",
          source_dir_purge => false, # Set to true to purge any existing file not present in $source_dir
        }

* Use custom template for main config file 

        class { "apache":
          template => "example42/apache/apache.conf.erb",      
        }

* Define custom options that can be used in a custom template without the
  need to add parameters to the apache class

        class { "apache":
          template => "example42/apache/apache.conf.erb",    
          options  => {
            'LogLevel' => 'INFO',
            'UsePAM'   => 'yes',
          },
        }

* Automaticallly include a custom subclass

        class { "apache:"
          my_class => 'apache::example42',
        }

## USAGE - Hiera Support
* Manage apache configuration using Hiera

```yaml
apache::template: 'modules/apache/apache2.conf.erb'
apache::options:
  timeout: '300'
  keepalive: 'On'
  maxkeepaliverequests: '100'
  keepalivetimeout: '5'
```

* Defining Apache resources using Hiera

```yaml
apache::virtualhost_hash:
  'mysite.com':
    documentroot: '/var/www/mysite.com'
    aliases: 'www.mysite.com'
apache::htpasswd_hash:
  'myuser':
    crypt_password: 'password1'
    htpasswd_file: '/etc/apache2/users.passwd'
apache::listen_hash:
  '8080':
    namevirtualhost: '*'
apache::module_hash:
  'status':
    ensure: present
```

## USAGE - Example42 extensions management 
* Activate puppi (recommended, but disabled by default)
  Note that this option requires the usage of Example42 puppi module

        class { "apache": 
          puppi    => true,
        }

* Activate puppi and use a custom puppi_helper template (to be provided separately with
  a puppi::helper define ) to customize the output of puppi commands 

        class { "apache":
          puppi        => true,
          puppi_helper => "myhelper", 
        }

* Activate automatic monitoring (recommended, but disabled by default)
  This option requires the usage of Example42 monitor and relevant monitor tools modules

        class { "apache":
          monitor      => true,
          monitor_tool => [ "nagios" , "monit" , "munin" ],
        }

* Activate automatic firewalling 
  This option requires the usage of Example42 firewall and relevant firewall tools modules

        class { "apache":       
          firewall      => true,
          firewall_tool => "iptables",
          firewall_src  => "10.42.0.0/24",
          firewall_dst  => "$ipaddress_eth0",
        }


[![Build Status](https://travis-ci.org/example42/puppet-apache.png?branch=master)](https://travis-ci.org/example42/puppet-apache)

[![Puppet Forge](http://img.shields.io/puppetforge/v/puppet/php.svg)](https://forge.puppetlabs.com/puppet/php)
[![Build Status](https://travis-ci.org/voxpupuli/puppet-php.svg?branch=master)](https://travis-ci.org/voxpupuli/puppet-php)

## Current Status
As the original creators of `puppet-php` are no longer maintaining the module, it has been handed over into the care of Vox Pupuli.
Please be sure to update all your links to the new location.

# voxpupuli/php Puppet Module

voxpupuli/php is a Puppet module for managing PHP with a strong focus
on php-fpm. The module aims to use sane defaults for the supported
architectures. We strive to support all recent versions of Debian,
Ubuntu, RedHat/CentOS, openSUSE/SLES and FreeBSD. Managing Apache
with `mod_php` is not supported.

This originally was a fork of [jippi/puppet-php](https://github.com/jippi/puppet-php)
(nodes-php on Puppet Forge) but has since been rewritten in large parts.

## Usage

Quickest way to get started is simply `include`'ing the _`php` class_.

```puppet
include '::php'
```

Or, you can override defaults and specify additional custom
configurations by declaring `class { '::php': }` with parameters:

```puppet
class { '::php':
  ensure       => latest,
  manage_repos => true,
  fpm          => true,
  dev          => true,
  composer     => true,
  pear         => true,
  phpunit      => false,
}
```

Optionally the PHP version or configuration root directory can be changed also:

```puppet
class { '::php::globals':
  php_version => '7.0',
  config_root => '/etc/php/7.0',
}->
class { '::php':
  manage_repos => true
}
```

There are more configuration options available. Please refer to the
auto-generated documentation at http://php.puppet.mayflower.de/.

### Defining `php.ini` settings

PHP configuration parameters in `php.ini` files can be defined as parameter
`settings` on the main `php` class, or `php::fpm` / `php::cli` classes,
or `php::extension` resources for each component independently.

These settings are written into their respective `php.ini` file. Global
settings in `php::settings` are merged with the settings of all components.
Please note that settings of extensions are always independent.

In the following example the PHP options and timezone will be set in
all PHP configurations, i.e. the PHP cli application and all php-fpm pools.

```puppet
  class { '::php':
    settings   => {
      'PHP/max_execution_time'  => '90',
      'PHP/max_input_time'      => '300',
      'PHP/memory_limit'        => '64M',
      'PHP/post_max_size'       => '32M',
      'PHP/upload_max_filesize' => '32M',
      'Date/date.timezone'      => 'Europe/Berlin',
    },
  }
```

### Installing extensions

PHP configuration parameters in `php.ini` files can be defined
as parameter `extensions` on the main `php` class. They are
activated for all activated SAPIs.

```puppet
  class { '::php':
    extensions => {
      bcmath    => { },
      imagick   => {
        provider => pecl,
      },
      xmlrpc    => { },
      memcached => {
        provider        => 'pecl',
        header_packages => [ 'libmemcached-devel', ],
      },
      apc       => {
        provider => 'pecl',
        settings => {
          'apc/stat'       => '1',
          'apc/stat_ctime' => '1',
        },
        sapi     => 'fpm',
      },
    },
  }
```

See [the documentation](http://php.puppet.mayflower.de/php/extension.html)
of the `php::extension` resource for all available parameters and default
values.

### Defining php-fpm pools

If different php-fpm pools are required, you can use `php::fpm::pool`
defined resource type. A single pool called `www` will be configured
by default. Specify additional pools like so:

```puppet
  php::fpm::pool { 'www2':
    listen => '127.0.1.1:9000',
  }
```

For an overview of all possible parameters for `php::fpm::pool` resources
please see [its documention](http://php.puppet.mayflower.de/php/fpm/pool.html).

### Overriding php-fpm user

By default, php-fpm is set up to run as Apache. If you need to customize that user, you can do that like so:

```puppet
  class { '::php':
    fpm_user  => 'nginx',
    fpm_group => 'nginx',
  }
```

### PHP with one FPM pool per user

This will create one vhost. $users is an array of people having php files at
$fqdn/$user. This codesnipped uses voxpupuli/php and voxpupuli/nginx to create
the vhost and one php fpm pool per user. This was tested on Archlinux with
nginx 1.13 and PHP 7.2.3.

```puppet
$users = ['bob', 'alice']

class { 'php':
   ensure       => 'present',
   manage_repos => false,
   fpm          => true,
   dev          => false,
   composer     => false,
   pear         => true,
   phpunit      => false,
   fpm_pools    => {},
}

include nginx

nginx::resource::server{$facts['fqdn']:
  www_root  => '/var/www',
  autoindex => 'on',
}
nginx::resource::location{'dontexportprivatedata':
  server        => $facts['fqdn'],
  location      => '~ /\.',
  location_deny => ['all'],
}
$users.each |$user| {
  # create one fpm pool. will be owned by the specific user
  # fpm socket will be owned by the nginx user 'http'
  php::fpm::pool{$user:
    user         => $user,
    group        => $user,
    listen_owner => 'http',
    listen_group => 'http',
    listen_mode  => '0660',
    listen       => "/var/run/php-fpm/${user}-fpm.sock",
  }
  nginx::resource::location { "${name}_root":
    ensure      => 'present',
    server      => $facts['fqdn'],
    location    => "~ .*${user}\/.*\.php$",
    index_files => ['index.php'],
    fastcgi     => "unix:/var/run/php-fpm/${user}-fpm.sock",
    include     => ['fastcgi.conf'],
  }
}
```

### Alternative examples using Hiera
Alternative to the Puppet DSL code examples above, you may optionally define your PHP configuration using Hiera.

Below are all the examples you see above, but defined in YAML format for use with Hiera.

```yaml
---
php::ensure: latest
php::manage_repos: true
php::fpm: true
php::fpm_user: 'nginx'
php::fpm_group: 'nginx'
php::dev: true
php::composer: true
php::pear: true
php::phpunit: false
php::settings:
  'PHP/max_execution_time': '90'
  'PHP/max_input_time': '300'
  'PHP/memory_limit': '64M'
  'PHP/post_max_size': '32M'
  'PHP/upload_max_filesize': '32M'
  'Date/date.timezone': 'Europe/Berlin'
php::extensions:
  bcmath: {}
  xmlrpc: {}
  imagick:
    provider: pecl
  memcached:
    provider: pecl
    header_packages:
      - libmemcached-dev
  apc:
    provider: pecl
    settings:
      'apc/stat': 1
      'apc/stat_ctime': 1
    sapi: 'fpm'
php::fpm::pools:
  www2:
    listen: '127.0.1.1:9000'
```

## Notes

### Debian squeeze & Ubuntu precise come with PHP 5.3

On Debian-based systems, we use `php5enmod` to enable extension-specific
configuration. This script is only present in `php5` packages beginning with
version 5.4. Furthermore, PHP 5.3 is not supported by upstream anymore.

We strongly suggest you use a recent PHP version, even if you're using an
older though still supported distribution release. Our default is to have
`php::manage_repos` enabled to add apt sources for
[Dotdeb](http://www.dotdeb.org/) on Debian and
[ppa:ondrej/php5](https://launchpad.net/~ondrej/+archive/ubuntu/php5/) on
Ubuntu with packages for the current stable PHP version closely tracking
upstream.

### Ubuntu systems and Ondřej's PPA

The older Ubuntu PPAs run by Ondřej have been deprecated (ondrej/php5, ondrej/php5.6)
in favor of a new PPA: ondrej/php which contains all 3 versions of PHP: 5.5, 5.6, and 7.0
Here's an example in hiera of getting PHP 5.6 installed with php-fpm, pear/pecl, and composer:

```
php::globals::php_version: '5.6'
php::fpm: true
php::dev: true
php::composer: true
php::pear: true
php::phpunit: false
```

If you do not specify a php version, in Ubuntu the default will be 7.0 if you are
running Xenial (16.04), otherwise PHP 5.6 will be installed (for other versions)

### Apache support

Apache with `mod_php` is not supported by this module. Please use
[puppetlabs/apache](https://forge.puppetlabs.com/puppetlabs/apache) instead.

We prefer using php-fpm. You can find an example Apache vhost in
`manifests/apache_vhost.pp` that shows you how to use `mod_proxy_fcgi` to
connect to php-fpm.

### Facts

We deliver a `phpversion` fact with this module. This is explicitly **NOT** intended
to be used within your puppet manifests as it will only work on your second puppet
run. Its intention is to make querying PHP versions per server easy via PuppetDB or Foreman.

### FreeBSD support

On FreeBSD systems we purge the system-wide `extensions.ini` in favour of
per-module configuration files.

Please also note that support for Composer and PHPUnit on FreeBSD is untested
and thus likely incomplete.

### Running the test suite

To run the tests install the ruby dependencies with `bundler` and execute
`rake`:

```
bundle install --path vendor/bundle
bundle exec rake
```

## Bugs & New Features

If you happen to stumble upon a bug, please feel free to create a pull request
with a fix (optionally with a test), and a description of the bug and how it
was resolved.

Or if you're not into coding, simply create an issue adding steps to let us
reproduce the bug and we will happily fix it.

If you have a good idea for a feature or how to improve this module in general,
please create an issue to discuss it. We are very open to feedback. Pull
requests are always welcome.

We hate orphaned and unmaintained Puppet modules as much as you do and
therefore promise that we will continue to maintain this module and keep
response times to issues short. If we happen to lose interest, we will write
a big fat warning into this README to let you know.

## License

The project is released under the permissive MIT license.

The source can be found at
[github.com/voxpupuli/puppet-php](https://github.com/voxpupuli/puppet-php/).

This Puppet module was originally maintained by some fellow puppeteers at
[Mayflower GmbH](https://mayflower.de) and is now maintained by
[Vox Pupuli](https://voxpupuli.org/).

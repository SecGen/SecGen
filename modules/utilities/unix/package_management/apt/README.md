# apt

#### Table of Contents


1. [Module Description - What the module does and why it is useful](#module-description)
1. [Setup - The basics of getting started with apt](#setup)
    * [What apt affects](#what-apt-affects)
    * [Beginning with apt](#beginning-with-apt)
1. [Usage - Configuration options and additional functionality](#usage)
    * [Add GPG keys](#add-gpg-keys)
    * [Prioritize backports](#prioritize-backports)
    * [Update the list of packages](#update-the-list-of-packages)
    * [Pin a specific release](#pin-a-specific-release) 
    * [Add a Personal Package Archive repository](#add-a-personal-package-archive-repository)
    * [Configure Apt from Hiera](#configure-apt-from-hiera)
    * [Replace the default sources.list file](#replace-the-default-sourceslist-file)
1. [Reference - An under-the-hood peek at what the module is doing and how](#reference)
1. [Limitations - OS compatibility, etc.](#limitations)
1. [Development - Guide for contributing to the module](#development)

## Module Description

The apt module lets you use Puppet to manage APT (Advanced Package Tool) sources, keys, and other configuration options.

APT is a package manager available on Debian, Ubuntu, and several other operating systems. The apt module provides a series of classes, defines, types, and facts to help you automate APT package management.

**Note**: For this module to correctly autodetect which version of Debian/Ubuntu (or derivative) you're running, you need to make sure the 'lsb-release' package is installed. We highly recommend you either make this part of your provisioning layer, if you run many Debian or derivative systems, or ensure that you have Facter 2.2.0 or later installed, which will pull this dependency in for you.

## Setup

### What apt affects

* Your system's `preferences` file and `preferences.d` directory
* Your system's `sources.list` file and `sources.list.d` directory
* System repositories
* Authentication keys

**Note:** This module offers `purge` parameters which, if set to `true`, **destroy** any configuration on the node's `sources.list(.d)` and `preferences(.d)` that you haven't declared through Puppet. The default for these parameters is `false`.

### Beginning with apt

To use the apt module with default parameters, declare the `apt` class.

```puppet
include apt
```

**Note:** The main `apt` class is required by all other classes, types, and defined types in this module. You must declare it whenever you use the module.

## Usage

### Add GPG keys

**Warning:** Using short key IDs presents a serious security issue, potentially leaving you open to collision attacks. We recommend you always use full fingerprints to identify your GPG keys. This module allows short keys, but issues a security warning if you use them.

Declare the `apt::key` defined type:

```puppet
apt::key { 'puppetlabs':
  id      => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
  server  => 'pgp.mit.edu',
  options => 'http-proxy="http://proxyuser:proxypass@example.org:3128"',
}
```

### Prioritize backports

```puppet
class { 'apt::backports':
  pin => 500,
}
```

By default, the `apt::backports` class drops a pin file for backports, pinning it to a priority of 200. This is lower than the normal default of 500, so packages with `ensure => latest` don't get upgraded from backports without your explicit permission.

If you raise the priority through the `pin` parameter to 500, normal policy goes into effect and Apt installs or upgrades to the newest version. This means that if a package is available from backports, it and its dependencies are pulled in from backports unless you explicitly set the `ensure` attribute of the `package` resource to `installed`/`present` or a specific version.

### Update the list of packages

By default, Puppet runs `apt-get update` on the first Puppet run after you include the `apt` class, and anytime `notify  => Exec['apt_update']` occurs; i.e., whenever config files get updated or other relevant changes occur. If you set `update['frequency']` to 'always', the update runs on every Puppet run. You can also set `update['frequency']` to 'daily' or 'weekly':

```puppet
class { 'apt':
  update => {
    frequency => 'daily',
  },
}
```
When `Exec['apt_update']` is triggered, it generates a `Notice` message. Because the default [logging level for agents](https://docs.puppet.com/puppet/latest/configuration.html#loglevel) is `notice`, this causes the repository update to appear in logs and agent reports. Some tools, such as [The Foreman](https://www.theforeman.org), report the update notice as a significant change. To eliminate these updates from reports, set the [loglevel](https://docs.puppet.com/puppet/latest/metaparameter.html#loglevel) metaparameter for `Exec['apt_update']` above the agent logging level:

```puppet
class { 'apt':
  update => {
    frequency => 'daily',
    loglevel  => 'debug',
  },
}
```

### Pin a specific release

```puppet
apt::pin { 'karmic': priority => 700 }
apt::pin { 'karmic-updates': priority => 700 }
apt::pin { 'karmic-security': priority => 700 }
```

You can also specify more complex pins using distribution properties:

```puppet
apt::pin { 'stable':
  priority        => -10,
  originator      => 'Debian',
  release_version => '3.0',
  component       => 'main',
  label           => 'Debian'
}
```

To pin multiple packages, pass them to the `packages` parameter as an array or a space-delimited string.

### Add a Personal Package Archive (PPA) repository

```puppet
apt::ppa { 'ppa:drizzle-developers/ppa': }
```

### Add an Apt source to `/etc/apt/sources.list.d/`

```puppet
apt::source { 'debian_unstable':
  comment  => 'This is the iWeb Debian unstable mirror',
  location => 'http://debian.mirror.iweb.ca/debian/',
  release  => 'unstable',
  repos    => 'main contrib non-free',
  pin      => '-10',
  key      => {
    'id'     => 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553',
    'server' => 'subkeys.pgp.net',
  },
  include  => {
    'src' => true,
    'deb' => true,
  },
}
```

To use the Puppet Apt repository as a source:

```puppet
apt::source { 'puppetlabs':
  location => 'http://apt.puppetlabs.com',
  repos    => 'main',
  key      => {
    'id'     => '6F6B15509CF8E59E6E469F327F438280EF8D349F',
    'server' => 'pgp.mit.edu',
  },
}
```

### Configure Apt from Hiera

Instead of specifying your sources directly as resources, you can instead just include the `apt` class, which will pick up the values automatically from hiera.

```yaml
apt::sources:
  'debian_unstable':
    comment: 'This is the iWeb Debian unstable mirror'
    location: 'http://debian.mirror.iweb.ca/debian/'
    release: 'unstable'
    repos: 'main contrib non-free'
    pin: '-10'
    key:
      id: 'A1BD8E9D78F7FE5C3E65D8AF8B48AD6246925553'
      server: 'subkeys.pgp.net'
    include:
      src: true
      deb: true

  'puppetlabs':
    location: 'http://apt.puppetlabs.com'
    repos: 'main'
    key:
      id: '6F6B15509CF8E59E6E469F327F438280EF8D349F'
      server: 'pgp.mit.edu'
```

### Replace the default `sources.list` file

The following example replaces the default `/etc/apt/sources.list`. Along with this code, be sure to use the `purge` parameter, or you might get duplicate source warnings when running Apt.

```puppet
apt::source { "archive.ubuntu.com-${lsbdistcodename}":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-security":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
  release  => "${lsbdistcodename}-security"
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-updates":
  location => 'http://archive.ubuntu.com/ubuntu',
  key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
  repos    => 'main universe multiverse restricted',
  release  => "${lsbdistcodename}-updates"
}

apt::source { "archive.ubuntu.com-${lsbdistcodename}-backports":
 location => 'http://archive.ubuntu.com/ubuntu',
 key      => '630239CC130E1A7FD81A27B140976EAF437D05B5',
 repos    => 'main universe multiverse restricted',
 release  => "${lsbdistcodename}-backports"
}
```

### Manage login configuration settings for an APT source or proxy in `/etc/apt/auth.conf`

Starting with APT version 1.5, you can define login configuration settings, such as
username and password, for APT sources or proxies that require authentication
in the `/etc/apt/auth.conf` file. This is preferable to embedding login
information directly in `source.list` entries, which are usually world-readable.

The `/etc/apt/auth.conf` file follows the format of netrc (used by ftp or
curl) and has restrictive file permissions. See [here](https://manpages.debian.org/testing/apt/apt_auth.conf.5.en.html) for details.

Use the optional `apt::auth_conf_entries` parameter to specify an array of hashes containing login configuration settings. These hashes may only contain the `machine`, `login` and `password` keys.

```puppet
class { 'apt':
  auth_conf_entries => [
    {
      'machine'  => 'apt-proxy.example.net',
      'login'    => 'proxylogin',
      'password' => 'proxypassword',
    },
    {
      'machine'  => 'apt.example.com/ubuntu',
      'login'    => 'reader',
      'password' => 'supersecret',
    },
  ],
}
```

## Reference

### Facts

* `apt_updates`: The number of installed packages with available updates from `upgrade`.

* `apt_dist_updates`: The number of installed packages with available updates from `dist-upgrade`.

* `apt_security_updates`: The number of installed packages with available security updates from `upgrade`.

* `apt_security_dist_updates`: The number of installed packages with available security updates from `dist-upgrade`.

* `apt_package_updates`: The names of all installed packages with available updates from `upgrade`. In Facter 2.0 and later this data is formatted as an array; in earlier versions it is a comma-delimited string.

* `apt_package_dist_updates`: The names of all installed packages with available updates from `dist-upgrade`. In Facter 2.0 and later this data is formatted as an array; in earlier versions it is a comma-delimited string.

* `apt_update_last_success`: The date, in epochtime, of the most recent successful `apt-get update` run (based on the mtime of  /var/lib/apt/periodic/update-success-stamp).

* `apt_reboot_required`: Determines if a reboot is necessary after updates have been installed.

### More Information

See [REFERENCE.md](https://github.com/puppetlabs/puppetlabs-apt/blob/master/REFERENCE.md) for all other reference documentation.

## Limitations

This module is not designed to be split across [run stages](https://docs.puppetlabs.com/puppet/latest/reference/lang_run_stages.html).

For an extensive list of supported operating systems, see [metadata.json](https://github.com/puppetlabs/puppetlabs-apt/blob/master/metadata.json)

### Adding new sources or PPAs

If you are adding a new source or PPA and trying to install packages from the new source or PPA on the same Puppet run, your `package` resource should depend on `Class['apt::update']`, as well as depending on the `Apt::Source` or the `Apt::Ppa`. You can also add [collectors](https://docs.puppetlabs.com/puppet/latest/reference/lang_collectors.html) to ensure that all packages happen after `apt::update`, but this can lead to dependency cycles and has implications for [virtual resources](https://docs.puppetlabs.com/puppet/latest/reference/lang_collectors.html#behavior). Before running the command below, ensure that all packages have the provider set to apt.

```puppet
Class['apt::update'] -> Package <| provider == 'apt' |>
```

## Development

Puppet modules on the Puppet Forge are open projects, and community contributions are essential for keeping them great. We can't access the huge number of platforms and myriad hardware, software, and deployment configurations that Puppet is intended to serve. We want to keep it as easy as possible to contribute changes so that our modules work in your environment. There are a few guidelines that we need contributors to follow so that we can have a chance of keeping on top of things.

For more information, see our [module contribution guide.](https://docs.puppetlabs.com/forge/contributing.html)

To see who's already involved, see the [list of contributors.](https://github.com/puppetlabs/puppetlabs-apt/graphs/contributors)

# Changelog

All notable changes to this project will be documented in this file.
Each new release typically also includes the latest modulesync defaults.
These should not affect the functionality of the module.

## [v5.2.0](https://github.com/voxpupuli/puppet-php/tree/v5.2.0) (2018-02-14)

[Full Changelog](https://github.com/voxpupuli/puppet-php/compare/v5.1.0...v5.2.0)

**Implemented enhancements:**

- add ubuntu 16.04 support [\#412](https://github.com/voxpupuli/puppet-php/pull/412) ([bastelfreak](https://github.com/bastelfreak))
- Add PHP 7.1 support on Debian [\#293](https://github.com/voxpupuli/puppet-php/pull/293) ([fstr](https://github.com/fstr))

**Fixed bugs:**

- Auto\_update not idempotent [\#402](https://github.com/voxpupuli/puppet-php/issues/402)
- use correct require arguments [\#415](https://github.com/voxpupuli/puppet-php/pull/415) ([bastelfreak](https://github.com/bastelfreak))
- fix composer auto\_update idempotency in case no update is available [\#408](https://github.com/voxpupuli/puppet-php/pull/408) ([joekohlsdorf](https://github.com/joekohlsdorf))
- Fixing wrong pear package name in Amazon Linux [\#399](https://github.com/voxpupuli/puppet-php/pull/399) ([gdurandvadas](https://github.com/gdurandvadas))

**Closed issues:**

- Upgrade to work with Puppet5 [\#406](https://github.com/voxpupuli/puppet-php/issues/406)
- php 7.2 + ubuntu 16.04 - pdo-mysql extension not installing correctly [\#405](https://github.com/voxpupuli/puppet-php/issues/405)
- config\_root parameter does nothing on RHEL7 [\#397](https://github.com/voxpupuli/puppet-php/issues/397)

**Merged pull requests:**

- Deprecate hiera\_hash functions [\#410](https://github.com/voxpupuli/puppet-php/pull/410) ([minorOffense](https://github.com/minorOffense))
- mark Puppet 5 as supported [\#407](https://github.com/voxpupuli/puppet-php/pull/407) ([joekohlsdorf](https://github.com/joekohlsdorf))
- Change default RedHat params to use config\_root [\#398](https://github.com/voxpupuli/puppet-php/pull/398) ([DALUofM](https://github.com/DALUofM))

## [v5.1.0](https://github.com/voxpupuli/puppet-php/tree/v5.1.0) (2017-11-10)

[Full Changelog](https://github.com/voxpupuli/puppet-php/compare/v5.0.0...v5.1.0)

**Fixed bugs:**

- Fix syntax issues with data types [\#385](https://github.com/voxpupuli/puppet-php/pull/385) ([craigwatson](https://github.com/craigwatson))
- fix ubuntu 17.04 version for php7 [\#383](https://github.com/voxpupuli/puppet-php/pull/383) ([arudat](https://github.com/arudat))
- Fix OS fact comparison for Ubuntu 12 and 14 [\#375](https://github.com/voxpupuli/puppet-php/pull/375) ([dbeckham](https://github.com/dbeckham))
- Fix OS facts usage when selecting repo class for Ubuntu systems [\#374](https://github.com/voxpupuli/puppet-php/pull/374) ([dbeckham](https://github.com/dbeckham))
- Confine pecl provider to where pear command is available [\#364](https://github.com/voxpupuli/puppet-php/pull/364) ([walkamongus](https://github.com/walkamongus))
- fix default value of php::fpm::pool::access\_log\_format [\#361](https://github.com/voxpupuli/puppet-php/pull/361) ([lesinigo](https://github.com/lesinigo))

**Closed issues:**

- Debian repository classes are being selected on Ubuntu systems [\#373](https://github.com/voxpupuli/puppet-php/issues/373)
- Changes in \#357 break Ubuntu version dependent resources [\#372](https://github.com/voxpupuli/puppet-php/issues/372)

**Merged pull requests:**

- release 5.1.0 [\#394](https://github.com/voxpupuli/puppet-php/pull/394) ([bastelfreak](https://github.com/bastelfreak))
- Proposed fix for failing parallel spec tests [\#386](https://github.com/voxpupuli/puppet-php/pull/386) ([wyardley](https://github.com/wyardley))
- update dependencies in metadata [\#379](https://github.com/voxpupuli/puppet-php/pull/379) ([mmoll](https://github.com/mmoll))
- Bump metadata.json version to 5.0.1-rc [\#377](https://github.com/voxpupuli/puppet-php/pull/377) ([dhollinger](https://github.com/dhollinger))
- bump dep on puppet/archive to '\< 3.0.0' [\#376](https://github.com/voxpupuli/puppet-php/pull/376) ([costela](https://github.com/costela))
- Release 5.0.0 [\#371](https://github.com/voxpupuli/puppet-php/pull/371) ([hunner](https://github.com/hunner))
- Backport of \#355  remove example42/yum dependency on puppet3 branch [\#366](https://github.com/voxpupuli/puppet-php/pull/366) ([LEDfan](https://github.com/LEDfan))
- Add missing php-fpm user and group class param docs [\#346](https://github.com/voxpupuli/puppet-php/pull/346) ([dbeckham](https://github.com/dbeckham))

## [v5.0.0](https://github.com/voxpupuli/puppet-php/tree/v5.0.0) (2017-08-07)
### Summary
This backwards-incompatible release drops puppet 3, PHP 5.5 on Ubuntu, and the deprecated `php::extension` parameter `pecl_source`. It improves much of the internal code quality, and adds several useful features the most interesting of which is probably the `php::extension` parameter `ini_prefix`.

### Changed
- Drop puppet 3 compatibility.
- Bumped puppetlabs-apt lower bound to 4.1.0
- Bumped puppetlabs-stdlib lower bound to 4.13.1

### Removed
- Deprecated `php::extension` define parameters `pecl_source`. Use `source` instead.
- PHP 5.5 support on ubuntu.

### Added
- `php` class parameters `fpm_user` and `fpm_group` to customize php-fpm user/group.
- `php::fpm` class parameters `user` and `group`.
- `php::fpm::pool` define parameter `pm_process_idle_timeout` and pool.conf `pm.process_idle_timeout` directive.
- `php::extension` class parameters `ini_prefix` and `install_options`.
- Archlinux compatibility.
- Bumped puppetlabs-apt upper bound to 5.0.0

### Fixed
- Replaced validate functions with data types.
- Linting issues.
- Replace legacy facts with facts hash.
- Simplify `php::extension`
- Only apt dependency when `manage_repos => true`
- No more example42/yum dependency

## 2017-02-11 Release [4.0.0]

This is the last release with Puppet3 support!
* Fix a bug turning `manage_repos` off on wheezy
* Fix a deprecation warning on `apt::key` when using `manage_repos` on wheezy (#110). This change requires puppetlabs/apt at >= 1.8.0
* Allow removal of config values (#124)
* Add `phpversion` fact, for querying through PuppetDB or Foreman (#119)
* Allow configuring the fpm pid file (#123)
* Add embedded SAPI support (#115)
* Add options to fpm config and pool configs (#139)
* Add parameter logic for PHP 7 on Ubuntu/Debian (#180)
* add SLES PHP 7.0 Support (#220)
* allow packaged extensions to be loaded as zend extensions
* Fix command to enable php extensions (#226)
* Fix many rucocop warnings
* Update module Ubuntu 14.04 default to official repository setup
* Fix dependency for extentions with no package source
* Allow packaged extensions to be loaded as Zend extensions
* Support using an http proxy for downloading composer
* Refactor classes php::fpm and php::fpm:service
* Manage apache/PHP configurations on Debian and RHEL systems
* use voxpupuli/archive to download composer
* respect $manage_repos, do not include ::apt if set to false
* Bump min version_requirement for Puppet + deps
* allow pipe param for pecl extensions
* Fix: composer auto_update: exec's environment must be array

### Breaking Changes
 * Deep merge `php::extensions` the same way as `php::settings`. This technically is a
   breaking change but should not affect many people.
 * PHP 5.6 is the default version on all systems now (except Ubuntu 16.04, where 7.0 is the default).
 * There's a php::globals class now, where global paramters (like the PHP version) are set. (#132)
 * Removal of php::repo::ubuntu::ppa (#218)

## 3.4.2
 * Fix a bug that changed the default of `php::manage_repos` to `false` on
   Debian-based operating systems except wheezy. It should be turned on by
   default. (#116)
 * Fix a bug that prevented reloading php-fpm on Ubuntu in some cases.
   (#117, #107)

## 3.4.1
 * Fix reloading php-fpm on Ubuntu trusty & utopic (#107)

## 3.4.0
 * New parameter `ppa` for class `php::repo::ubuntu` to specify the ppa
   name to use. We default to `ondrej/php5-oldstable` for precise and
   `ondrej/php5` otherwise.
 * New parameter `include` for `php::fpm::pool` resources to specify
   custom configuration files.

## 3.3.1
 * Make `systemd_interval` parameter for class `php::fpm::config` optional

## 3.3.0
 * `php::extension` resources:
   * New boolean parameter `settings_prefix` to automatically prefix all
     settings keys with the extensions names. Defaults to false to ensurre
     the current behaviour.
   * New string parameter `so_name` to set the DSO name of an extension if
     it doesn't match the package name.
   * New string parameter `php_api_version` to set a custom api version. If
     not `undef`, the `so_name` is prefixed with the full module path in the
     ini file. Defaults to `undef`.
 * The default of the parameter `listen_allowed_clients` of `php::fpm::pool`
   resources is now `undef` instead of `'127.0.0.1'`. This way it is more
   intuitive to change the default tcp listening socket at `127.0.0.1:9000`
   to a unix socket by only setting the `listen` parameter instead of
   additionally needing to unset `listen_allowed_clients`. This has no
   security implications.
 * New parameters for the `php::fpm::config` class:
   * `error_log`
   * `syslog_facility`
   * `syslog_ident`
   * `systemd_interval`
 * A bug that prevented merging the global `php::settings` parameter into
   SAPI configs for `php::cli` and `php::fpm` was fixed.
 * The dotdeb repos are now only installed for Debian wheezy as Debian jessie
   has a sufficiently recent PHP version.

## 3.2.2
 * Fix a typo in hiera keys `php::settings` & `php::fpm::settings` (#83)

## 3.2.1
 * Fixed default `yum_repo` key in `php::repo::redhat`
 * On Ubuntu precise we now use the ondrej/php5-oldstable ppa. This can be
   manually enabled with by setting `$php::repo::ubuntu::oldstable` to
   `true`.
 * `$php::ensure` now defaults to `present` instead of `latest`. Though,
   strictly speaking, this represents a functional change, we consider this
   to be a bugfix because automatic updates should be enabled explicitely.
 * `$php::ensure` is not anymore passed to `php::extension` resources as
   default ensure parameter because this doesn't make sense.

## 3.2.0
 * Support for FreeBSD added by Frank Wall
 * RedHat now uses remi-php56 yum repo by default
 * The resource `php::fpm::pool` is now public, you can use it in your
   manifests without using `$php::fpm::pools`
 * We now have autogenerated documentation using `puppetlabs/strings`

## 3.1.0
 * New parameter `pool_purge` for `php::extension` to remove files not
   managed by puppet from the pool directory.
 * The `pecl_source` parameter for `php::extension` was renamend to
   `source` because it is also useful for PEAR extensions.
   `pecl_source` can still be used but is deprecated and will be
   removed in the next major release.
 * Parameters referring to time in `php::fpm::config` can now be
   specified with units (i.e. `'60s'`, `'1d'`):
   * `emergency_restart_threshold`
   * `emergency_restart_interval`
   * `process_control_timeout`
 * The PEAR version is not independant of `$php::ensure` and can be
   configured with `$php::pear_ensure`
 * Give special thanks to the contributors of this release:
   * Petr Sedlacek
   * Sherlan Moriah

## 3.0.1
 * Fix typo in package suffix for php-fpm on RHEL in params.pp

## 3.0.0
 * Removes `$php::fpm::pool::error_log`. Use the `php_admin_flag` and
   `php_admin_value` parameters to set the php settings `log_errors` and
   `error_log` instead.
 * Removes support for PHP 5.3 on Debian-based systems. See the notes in the
   README for more information.
 * Removes the `php_version` fact which had only worked on the later puppet runs.
 * Moves CLI-package handling to `php::packages`
 * Allows changing the package prefix via `php::package_prefix`.
 * Moves FPM-package handling from `php::fpm::package` to `php::fpm`
 * Changes `php::packages`, so that `php::packages::packages` becomes
   `php::packages::names` and are installed and `php::packages::names_to_prefix`
   are installed prefixed by `php::package_prefix`.
 * PHPUnit is now installed as phar in the same way composer is installed,
   causing all parameters to change
 * The `php::extension` resource has a new parameter: `zend`. If set to true,
   exenstions that were installed with pecl are loaded with `zend_extension`.

## 2.0.4
 * Style fixes all over the place
 * Module dependencies are now bound to the current major version

## 2.0.3
 * Some issues & bugs with extensions were fixed
 * If you set the `provider` parameter of an extension to `"none"`, no
   extension packages will be installed
 * The EPEL yum repo has been added for RedHat systems

## 2.0.2
 * Adds support for `header_packages` on all extensions
 * Adds `install_options` to pear package provider

## 2.0.1
 * This is a pure bug fix release
   * Fix for CVE 2014-0185 (https://bugs.php.net/bug.php?id=67060)

## 2.0.0
 * Remove augeas and switch to puppetlabs/inifile for configs
   * Old: `settings => [‘set PHP/short_open_tag On‘]`
   * New: `settings => {‘PHP/short_open_tag’ => ‘On‘}`
 * Settings parmeter cleanups
   * The parameter `config` of `php::extension` resources is now called `settings`
   * The parameters `user` and `group` of `php::fpm` have been moved to `php::fpm::config`
   * New parameter `php::settings` for global settings (i.e. CLI & FPM)
 * New parameter `php::cli` to disable CLI if supported

## 1.1.2
 * SLES: PHP 5.5 will now be installed
 * Pecl extensions now autoload the .so based on $name instead of $title

## 1.1.1
 * some nasty bugs with the pecl php::extension provider were fixed
 * php::extension now has a new pecl_source parameter for specifying custom
   source channels for the pecl provider

## 1.1.0
 * add phpunit to main class
 * fix variable access for augeas

## 1.0.2
 * use correct suse apache service name
 * fix anchoring of augeas

## 1.0.1
 * fixes #9 undefined pool_base_dir

## 1.0.0
Initial release

[4.1.0]: https://github.com/olivierlacan/keep-a-changelog/compare/v4.0.0...v4.1.0
[4.0.0]: https://github.com/olivierlacan/keep-a-changelog/compare/v3.4.2...v4.0.0


\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
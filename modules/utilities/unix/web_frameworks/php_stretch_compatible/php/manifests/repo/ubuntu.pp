# Configure ubuntu ppa
#
# === Parameters
#
# [*version*]
#   PHP version to manage (e.g. 5.6)
#
class php::repo::ubuntu (
  $version   = undef,
) {
  include '::apt'

  if($version == undef) {
    $version_real = '5.6'
  } else {
    $version_real = $version
  }

  if ($version_real == '5.5') {
    fail('PHP 5.5 is no longer available for download')
  }
  assert_type(Pattern[/^\d\.\d/], $version_real)

  $version_repo = $version_real ? {
    '5.4' => 'ondrej/php5-oldstable',
    '5.6' => 'ondrej/php',
    '7.0' => 'ondrej/php'
  }

  ::apt::ppa { "ppa:${version_repo}":
    package_manage => true,
  }
}

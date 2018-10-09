# Install phpunit package manager
#
# === Parameters
#
# [*max_age*]
#   Defines number of days after which phpunit should be updated
#
# [*source*]
#   Holds URL to the phpunit source file
#
# [*path*]
#   Holds path to the phpunit executable
#
class php::phpunit::auto_update (
  $max_age,
  $source,
  $path,
) {

  assert_private()

  exec { 'update phpunit':
    command => "wget ${source} -O ${path}",
    onlyif  => "test `find '${path}' -mtime +${max_age}`",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/', '/usr/local/bin', '/usr/local/sbin' ],
    require => File[$path],
  }
}

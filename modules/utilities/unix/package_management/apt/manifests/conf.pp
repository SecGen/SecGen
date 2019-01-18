# @summary Specifies a custom Apt configuration file.
#
# @param content
#   Required unless `ensure` is set to 'absent'. Directly supplies content for the configuration file.
#
# @param ensure
#    Specifies whether the configuration file should exist. Valid options: 'present' and 'absent'. 
#
# @param priority
#   Determines the order in which Apt processes the configuration file. Files with lower priority numbers are loaded first. 
#   Valid options: a string containing an integer or an integer.
#
# @param notify_update
#   Specifies whether to trigger an `apt-get update` run.
#
define apt::conf (
  Optional[String] $content          = undef,
  Enum['present', 'absent'] $ensure  = present,
  Variant[String, Integer] $priority = 50,
  Optional[Boolean] $notify_update   = undef,
) {

  unless $ensure == 'absent' {
    unless $content {
      fail(translate('Need to pass in content parameter'))
    }
  }

  $confheadertmp = epp('apt/_conf_header.epp')
  apt::setting { "conf-${name}":
    ensure        => $ensure,
    priority      => $priority,
    content       => "${confheadertmp}${content}",
    notify_update => $notify_update,
  }
}

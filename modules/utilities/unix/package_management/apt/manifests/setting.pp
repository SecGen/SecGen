# @summary Manages Apt configuration files.
#
# @see https://docs.puppetlabs.com/references/latest/type.html#file-attributes for more information on source and content parameters
#
# @param priority
#   Determines the order in which Apt processes the configuration file. Files with higher priority numbers are loaded first.
#
# @param ensure
#   Specifies whether the file should exist. Valid options: 'present', 'absent', and 'file'.
#
# @param source
#   Required, unless `content` is set. Specifies a source file to supply the content of the configuration file. Cannot be used in combination 
#   with `content`. Valid options: see link above for Puppet's native file type source attribute.
#
# @param content
#   Required, unless `source` is set. Directly supplies content for the configuration file. Cannot be used in combination with `source`. Valid 
#   options: see link above for Puppet's native file type content attribute.
#
# @param notify_update
#   Specifies whether to trigger an `apt-get update` run.
#
define apt::setting (
  Variant[String, Integer, Array] $priority           = 50,
  Optional[Enum['file', 'present', 'absent']] $ensure = file,
  Optional[String] $source                            = undef,
  Optional[String] $content                           = undef,
  Boolean $notify_update                              = true,
) {

  if $content and $source {
    fail(translate('apt::setting cannot have both content and source'))
  }

  if !$content and !$source {
    fail(translate('apt::setting needs either of content or source'))
  }

  $title_array = split($title, '-')
  $setting_type = $title_array[0]
  $base_name = join(delete_at($title_array, 0), '-')

  assert_type(Pattern[/\Aconf\z/, /\Apref\z/, /\Alist\z/], $setting_type) |$a, $b| {
    fail(translate("apt::setting resource name/title must start with either 'conf-', 'pref-' or 'list-'"))
  }

  if $priority !~ Integer {
    # need this to allow zero-padded priority.
    assert_type(Pattern[/^\d+$/], $priority) |$a, $b| {
      fail(translate('apt::setting priority must be an integer or a zero-padded integer'))
    }
  }

  if ($setting_type == 'list') or ($setting_type == 'pref') {
    $_priority = ''
  } else {
    $_priority = $priority
  }

  $_path = $::apt::config_files[$setting_type]['path']
  $_ext  = $::apt::config_files[$setting_type]['ext']

  if $notify_update {
    $_notify = Class['apt::update']
  } else {
    $_notify = undef
  }

  file { "${_path}/${_priority}${base_name}${_ext}":
    ensure  => $ensure,
    owner   => 'root',
    group   => 'root',
    mode    => '0644',
    content => $content,
    source  => $source,
    notify  => $_notify,
  }
}

class kde_minimal::config {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)
  $accounts = $secgen_params['accounts']
  $autologin_user = $secgen_params['autologin_user'][0]
  $autostart_konsole = str2bool($secgen_params['autostart_konsole'][0])

  if $autologin_user != "false" {
    file { "/etc/kde4/kdm/kdmrc":
      ensure => file,
      content => template('kde_minimal/kdmrc.erb'),
    }
  }

  $accounts.each |$raw_account| {
    $account = parsejson($raw_account)
    $username = $account['username']

    # autostart konsole
    if $autostart_konsole {
      file { ["/home/$username/.config/", "/home/$username/.config/autostart/"]:
        ensure => directory,
        owner  => $username,
        group  => $username,
      }

      file { "/home/$username/.config/autostart/org.kde.konsole.desktop":
        ensure => file,
        source => 'puppet:///modules/kde_minimal/org.kde.konsole.desktop',
        owner  => $username,
        group  => $username,
      }
    }
  }
}

class unrealirc::vulnerabilities {

  if defined('unrealirc_3281_backdoor') {

    $archive = "unrealircd_3.2.8.1.vuln.tar.gz"

    file { "/tmp/${archive}":
      owner  => root,
      group  => root,
      mode   => '0775',
      ensure => file,
      source => "puppet:///modules/unrealirc_3281_backdoor/${archive}",
    }

    # Using mv here to avoid naming conflicts with the unrealirc::install file reosurce for /tmp/unreal3.2.8.1.tar.gz
    exec { 'move-unreal3281-vuln-tar':
      command => "/bin/mv /tmp/${archive} /tmp/${unrealirc::filename}.tar.gz",
      before  => Exec['extract-unrealirc'],
    }
  }
}
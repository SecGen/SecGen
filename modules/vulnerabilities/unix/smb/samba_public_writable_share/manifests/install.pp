class samba_public_writable_share::install {
  include samba

  $secgen_parameters = parsejson($::json_inputs)
  $storage_directory = $secgen_parameters['storage_directory'][0]
  $leaked_filename = $secgen_parameters['leaked_filename'][0]

  # Ensure the storage directory exists
  file { $storage_directory:
    ensure => directory,
  }

  # Add store to .conf
  file { '/etc/samba/smb_pws.conf':
    ensure => file,
    content => template ('samba/smb_share.conf.erb')
  }
 concat { '/etc/samba/smb.conf':
   ensure => present,
 }
 concat::fragment { 'smb-conf-base':
   source => '/etc/samba/smb.conf',
   target => '/etc/samba/smb.conf',
   order => '01',
 }
 concat::fragment { 'smb-conf-public-share-definition':
   source => '/etc/samba/smb_pws.conf',
   target => '/etc/samba/smb.conf',
   order => '02',
 }

  # Leak file and share extras
  file { "$storage_directory/$leaked_filename":
    ensure  => present,
    owner   => 'root',
    group   => 'root',
    mode    => '0777',
    content  => template('samba/overshare.erb')
  }

}
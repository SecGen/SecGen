class { 'vsftpd':
  anonymous_enable  => 'YES',
  write_enable      => 'YES',
  ftpd_banner       => 'Marmotte FTP Server',
  chroot_local_user => 'YES',
}

include vsftpd
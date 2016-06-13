node 'server.example.com' {
  class { 'samba::server':
    workgroup     => 'example',
    server_string => "Example Samba Server",
    interfaces    => "eth0 lo",
    security      => 'share'
  }

  samba::server::share { 'example-share':
    comment              => 'Example Share',
    path                 => '/var',
    guest_only           => true,
    guest_ok             => true,
    guest_account        => "guest",
    browsable            => false,
    create_mask          => 0777,
    force_create_mask    => 0777,
    directory_mask       => 0777,
    force_directory_mask => 0777,
    force_group          => 'group',
    force_user           => 'user',
    copy                 => 'some-other-share',
  }
}



# node 'server.example.com' {
#   class {'samba::server':
#     workgroup => 'example',
#     server_string => "Example Samba Server",
#     interfaces => "eth0 lo",
#     security => 'ads'
#   }
#
#   samba::server::share {'ri-storage':
#     comment           => 'RBTH User Storage',
#     path              => "$smb_share",
#     browsable         => true,
#     writable          => true,
#     create_mask       => 0770,
#     directory_mask    => 0770,
#   }
#
#   class { 'samba::server::ads':
#     winbind_acct    => $::domain_admin,
#     winbind_pass    => $::admin_password,
#     realm           => 'EXAMPLE.COM',
#     nsswitch        => true,
#     target_ou       => "Nix_Mashine"
#   }
# }

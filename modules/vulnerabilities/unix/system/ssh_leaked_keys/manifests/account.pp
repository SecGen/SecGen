define ssh_leaked_keys::account($username, $password, $strings_to_leak, $leaked_filenames) {
  ::accounts::user { $username:
    shell      => '/bin/bash',
    password   => pw_hash($password, 'SHA-512', 'mysalt'),
    managehome => true,
    home_mode  => '0755',
    sshkeys    => [ "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQCj2gbaOju+u3bdwiMcd2JRgdFqmgaMyRhj6eCu2f8aBfZZVSyrNw+aOzlbILIjIlCHjhUfY/56n6XnH/iaLVr8IpGIz43VuxZ0/dKrjQerbbrJKg25rlDE+kbBwfdBeK3XkJj0d35ON6hkks7jU6scKy4t5LJZ+vnuISs98Gz1t9qjcdHEV5eYNdRjX+FzPW1bTI/RHHAZ53upuEpNArTITn29tnhp5sybDTUba6T09u2rowijn3s46mvqF9NXPZMnjghsStbvHtCYuY8uXNMJCyQzjxsUJbTMuqu2DZ2t2cGnC1wITE/4ZCpNC9gBLQ4ssJVbe0pF3lLJnMx3ggPV $username" ],
  }

  # Leak strings in a text file in the users home directory
  ::secgen_functions::leak_files { "$username-file-leak":
    storage_directory => "/home/$username/",
    leaked_filenames  => $leaked_filenames,
    strings_to_leak   => $strings_to_leak,
    owner             => $username,
    group             => $username,
    mode              => '0600',
    leaked_from       => "accounts_$username",
  }

  file { "/home/$username/.ssh.tar.gz":
    owner  => $username,
    group  => $username,
    mode   => '0644',
    ensure => file,
    source => 'puppet:///modules/ssh_leaked_keys/.ssh.tar.gz',
    notify => Exec['unpack'],
  }

  exec { 'unpack':
    cwd     => "/home/$username/",
    command => "tar -xzf /home/$username/.ssh.tar.gz",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
    notify => Exec['setperm'],
  }

  exec { 'setperm':
    cwd     => "/home/$username/",
    command => "sudo chown -R $username:$username /home/$username/.ssh",
    path    => [ '/bin/', '/sbin/' , '/usr/bin/', '/usr/sbin/' ],
  }
}
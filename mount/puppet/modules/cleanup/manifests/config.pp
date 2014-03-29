  class cleanup::config {
# removes bash history
  exec { "rm":
      command => "rm -rf .bash_history",
      path    => "/bin/",
  }
# finds every file and modifies with date may 2006
  exec { "find":
	  command => "find / -exec touch -d '17 May 2006 14:16' {} \\;",
	  path => "/usr/bin/",
  }
# disables eth1 which runs the public network for each vulnerable machine 
#  vagrant runs over 10.0 for eth0 .. eth1 for public .. and eth2 for private.

    exec { "ifconfig":
	  command => "ifconfig eth1 down",
	  path => "/sbin/",
  }
# changes default vagrant password, would kind of be pointless if they could just ssh to vagrant/vagrant :P

    user { 'vagrant':
    password => 'superdupersecurepassword',
  }

  # or you can remove the user entierly, up to you 'but if you are playing around with vagrant might cause problems'
  #use this option only when you are rolling out to users.

  #   user { 'vagrant':
  #     uid => '444',
  #     gid => '444',
  #     ensure => 'absent',
  #     password => '!'
  # }

}
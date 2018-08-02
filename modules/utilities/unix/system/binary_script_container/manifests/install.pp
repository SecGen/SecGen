class binary_script_container::install {

  # Create temp install directory
  file { '/root/tmp':
    ensure => directory,
  }

  # Move wrapper.c onto box
  file { "/root/tmp/suid.c":
    ensure => file,
    source => 'puppet:///modules/binary_script_container/wrapper.c',
  }

  # Make and install
  exec { "wrapper make install":
    command => 'make suid; install -m a+rx,u+ws -s ./suid /usr/local/bin/suid',
    cwd => '/root/tmp',
    path => '/bin:/sbin:/usr/bin:/usr/sbin',
  }

  # Create group for test   TODO: remove me
  group { 'test':
    ensure => present
  }

  file { '/home/tmp':
    ensure => directory,
  }

  # Move test file onto box   TODO: remove me
  file { "/home/tmp/test.sh":
    ensure => file,
    source => 'puppet:///modules/binary_script_container/test.sh',
    group => 'test',
    mode    => '2775',
    require => [Group['test'],File['/home/tmp']],
  }


  # Test: add a flag file with a group  TODO: remove me
  ::secgen_functions::leak_files { "flag-file-leak":
    storage_directory => "/home/tmp/",
    leaked_filenames  => ['flag'],
    strings_to_leak   => ['flag{wayy!!!}'],
    owner             => 'root',
    group             => 'test',
    mode              => '0440',
    leaked_from       => "binary_script_container_flag",
    require           => [Group['test'], File["/home/tmp/test.sh"]],
  }


  # Remove temp install directory


}
class maze::install {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)
  $challenge_name = $secgen_params['test'][0]
  $maze_dir = '/vagrant/src/maze'

  Exec { path => '/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/go/bin:/vagrant/bin' }

  ::secgen_functions::create_directory { "create_$challenge_directory":
    path   => $maze_dir,
    notify => File['copy maze dir'],
  }

  file { 'copy maze dir':
    path    => $maze_dir,
    ensure  => directory,
    recurse => true,
    source  => 'puppet:///modules/maze/maze-master',
    notify  => Exec['make maze'],
  }

  exec { 'make maze':
    cwd     => $maze_dir,
    command => 'env DEPNOLOCK=1 make',
    notify  => Exec['install maze'],
  }

  exec { 'install maze':
    cwd     => "$maze_dir/build",
    command => 'install maze /usr/local/bin',
  }
}
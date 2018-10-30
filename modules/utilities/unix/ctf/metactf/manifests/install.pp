class metactf::install {
  $secgen_params = secgen_functions::get_parameters($::base64_inputs_file)
  $install_dir = '/opt/metactf'

  Exec { path => ['/bin', '/usr/bin', '/usr/local/bin', '/sbin', '/usr/sbin'] }

  ensure_packages('build-essential')
  ensure_packages('gcc-multilib')

  file { $install_dir:
    ensure  => directory,
    recurse => true,
    source  => 'puppet:///modules/metactf/repository',
  }

  exec { 'set install.sh mode':
    command => "chmod +x $install_dir/install.sh",
  }

  exec { 'install metactf dependencies':
    command => "/bin/bash $install_dir/install.sh"
  }

  # For now just build all of the binaries.
  exec { 'build src_angr binaries':
    cwd     => "$install_dir/src_angr/",
    command => "/usr/bin/make",
  }

  # TODO: Build src_csp
  # TODO: Build src_malware

}
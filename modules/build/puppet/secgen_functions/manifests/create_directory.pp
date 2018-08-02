define secgen_functions::create_directory($path){
  exec  { "secgen_create_directory_$path":
    path => '/bin:/sbin:/usr/bin:/usr/sbin',
    command => "mkdir -p $path"
  }
}

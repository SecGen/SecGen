class userspice_43 {
  class { 'userspice_43::apache': } ~>
  class { 'userspice_43::install': } 
#~>
#  cron { 'run userspice config script':
#    command => '/bin/bash /userspice.sh',
#    minute => [0,5,10,15,20,25,30,35,40,45,50,55]
#  }
}

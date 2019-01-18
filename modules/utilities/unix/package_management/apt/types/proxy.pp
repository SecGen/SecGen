# @summary Configures Apt to connect to a proxy server.
# 
# @param ensure
#   Specifies whether the proxy should exist. Valid options: 'file', 'present', and 'absent'. Prefer 'file' over 'present'.
#
# @param host
#   Specifies a proxy host to be stored in `/etc/apt/apt.conf.d/01proxy`. Valid options: a string containing a hostname.
#
# @param port
#   Specifies a proxy port to be stored in `/etc/apt/apt.conf.d/01proxy`. Valid options: an integer containing a port number.
#
# @param https
#   Specifies whether to enable https proxies.
#
# @param direct
#   Specifies whether or not to use a `DIRECT` https proxy if http proxy is used but https is not.
#
type Apt::Proxy = Struct[
  {
    ensure => Optional[Enum['file', 'present', 'absent']],
    host   => Optional[String],
    port   => Optional[Integer[0, 65535]],
    https  => Optional[Boolean],
    direct => Optional[Boolean],
  }
]

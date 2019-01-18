# @summary Login configuration settings that are recorded in the file `/etc/apt/auth.conf`.
#
# @see https://manpages.debian.org/testing/apt/apt_auth.conf.5.en.html for more information
#
# @param machine
#   Hostname of machine to connect to.
#
# @param login
#   Specifies the username to connect with.
#
# @param password
#   Specifies the password to connect with.
#
type Apt::Auth_conf_entry = Struct[
  { 
    machine => String[1], 
    login => String, 
    password => String 
  }
]

class { 'python':
  version    => 'python3',
  pip        => 'present',
  dev        => 'absent',
  virtualenv => 'absent',
  gunicorn   => 'absent',
}
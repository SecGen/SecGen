class chkrootkit::configure {
  # Add cron job for chkrootkit, run it every minute so it's exploitable without a wait
  cron { 'chkrootkit':
    command => '/usr/sbin/chkrootkit',
    user    => 'root',
    hour    => '*',
    minute  => '*',
  }
}
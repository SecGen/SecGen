<h2>Statistics</h2>
<div class="row "> <!-- rows for Info Panels -->
    <div class="col-xs-12 col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>All Users</strong> <span class="small">(Who have logged in)</span></div>
      <div class="panel-body text-center">
        <div class="row">
          <div class="col-xs-3 "><h3><?=$hourCount?></h3><p>per hour</p></div>
          <div class="col-xs-3"><h3><?=$dayCount?></h3><p>per day</p></div>
          <div class="col-xs-3 "><h3><?=$weekCount?></h3><p>per week</p></div>
          <div class="col-xs-3 "><h3><?=$monthCount?></h3><p>per month</p></div>
        </div>
      </div>
    </div><!--/panel-->


    <div class="panel panel-default">
      <div class="panel-heading"><strong>All Visitors</strong> <span class="small">(Whether logged in or not)</span></div>
      <div class="panel-body">
        <?php  if($settings->track_guest == 1){ ?>
          <?="In the last 30 minutes, the unique visitor count was ".count_users()."<br>";?>
        <?php }else{ ?>
          Guest tracking off. Turn "Track Guests" on below for advanced tracking statistics.
        <?php } ?>
      </div>
    </div><!--/panel-->

  </div> <!-- /col -->

<div class="col-xs-12 col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading"><strong>Logged In Users</strong> <span class="small">(past 24 hours)</span></div>
    <div class="panel-body">
      <div class="uvistable table-responsive">
        <table class="table">
          <?php if($settings->track_guest == 1){ ?>
            <thead><tr><th>Username</th><th>IP</th><th>Last Activity</th></tr></thead>
            <tbody>

              <?php foreach($recentUsers as $v1){
                $user_id=$v1->user_id;
                $username=echousername($v1->user_id);
                $timestamp=date("Y-m-d H:i:s",$v1->timestamp);
                $ip=$v1->ip;

                if ($user_id==0){
                  $username="guest";
                }

                if ($user_id==0){?>
                  <tr><td><?=$username?></td><td><?=$ip?></td><td><?=$timestamp?></td></tr>
                <?php }else{ ?>
                  <tr><td><a href="admin_user.php?id=<?=$user_id?>"><?=$username?></a></td><td><?=$ip?></td><td><?=$timestamp?></td></tr>
                <?php } ?>

              <?php } ?>

            </tbody>
          <?php }else{echo 'Guest tracking off. Turn "Track Guests" on below for advanced tracking statistics.';} ?>
        </table>
      </div>
    </div>
  </div><!--/panel-->

  <div class="panel panel-default">
    <div class="panel-heading"><strong>Security Events</strong><span align="right" class="small"><a href="tomfoolery.php"> (View Logs)</a></span></div>
    <div class="panel-body" align="center">
      There have been<br>
      <h2><?=$tomC?></h2>
      security events triggered
    </div>
  </div><!--/panel-->
</div>
</div>

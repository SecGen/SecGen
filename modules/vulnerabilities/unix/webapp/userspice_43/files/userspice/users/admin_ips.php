<?php
/*
UserSpice 4
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
?>
<?php require_once 'init.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/header.php'; ?>
<?php require_once $abs_us_root.$us_url_root.'users/includes/navigation.php'; ?>
<?php if (!securePage($_SERVER['PHP_SELF'])){die();} ?>

<?php
$wl = $db->query("SELECT * FROM us_ip_whitelist")->results();
$bl = $db->query("SELECT * FROM us_ip_blacklist")->results();
if(!empty($_POST)){
if(!empty($_POST['newIP'])){
$ip = Input::get('ip');
$wl = Input::get('type');
if(filter_var($ip, FILTER_VALIDATE_IP)){
if($wl == 'whitelist'){
  logger($user->data()->id,"Setting Change","Whitelisted ".$ip);
  $db->insert('us_ip_whitelist',['ip'=>$ip]);
  Redirect::to('admin_ips.php?err=New+IP+Whitelisted');
}else{
  logger($user->data()->id,"Setting Change","Blacklisted ".$ip);
  $db->insert('us_ip_blacklist',['ip'=>$ip]);
  Redirect::to('admin_ips.php?err=New+IP+Blacklisted');
}
}else{
  Redirect::to('admin_ips.php?err=Invalid+IP+address');
}
}

if(!empty($_POST['delete'])){
  foreach($_POST['deletewhite'] as $k=>$v){
    $ip = $db->query("SELECT ip FROM us_ip_whitelist WHERE id = ?",array($v))->first();
      logger($user->data()->id,"Setting Change","Deleted ".$ip->ip." from whitelist");
    $db->deleteById('us_ip_whitelist',$v);
  }
  foreach($_POST['deleteblack'] as $k=>$v){
    $ip = $db->query("SELECT ip FROM us_ip_blacklist WHERE id = ?",array($v))->first();
      logger($user->data()->id,"Setting Change","Deleted ".$ip->ip." from blacklist");
    $db->deleteById('us_ip_blacklist',$v);
  }
  Redirect::to('admin_ips.php?err=IP(s) Deleted');
}



}

  ?>
  <div id="page-wrapper">

    <div class="container-fluid">

      <!-- Page Heading -->

      <div class="row">

        <div class="col-xs-6">
          <h1>Manage IP Addresses</h1>
          <p>Note: Whitelist overrides Blacklist</p>
        </div>
        <div class="col-xs-6">
<form class="" action="" method="post">
          <table class="table">
            <thead>
              <tr>
                <td>IP</td><td>Whitelist</td><td>Blacklist</td><td>Submit</td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="text" name="ip" value="" placeholder="Enter IP Address" required></td>
                <td><input type="radio" name="type" value="whitelist" required></td>
                <td><input type="radio" name="type" value="blacklist"></td>
                <td><input type="submit" name="newIP" value="Submit" class="btn btn-primary"></td>
              </tr>
            </tbody>
          </table>
</form>
<form class="" action="" method="post">
<input class="btn btn-danger" type="submit" name="delete" value="Delete Selected IPs">
        </div>
      </div>

<div class="row">
        <div class="col-xs-12 col-md-4">
          <h3>Whitelisted IP Addresses</h3>

          <table class="table table-striped">
            <thead>
              <tr>
                <th>IP Address</th><th>Delete</th>
              </tr>
            </thead>

            <tbody>
            <?php foreach($wl as $b){ ?>
              <tr>
                <td><?=$b->ip?></td>
                <td><input type="checkbox" name="deletewhite[<?=$b->id?>]" value="<?=$b->id?>"></td>
              </tr>
          <?php }?>
            </tbody>
          </table>
        </div>

        <div class="col-xs-12 col-md-8">
          <h3>Blacklisted IP Addresses</h3>
          <table class="table table-striped">
            <thead>
              <tr>
                <th>IP Address</th><th>Reason</th><th>Last User</th><th>Delete</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($bl as $b){ ?>
              <tr>
                <td><?=$b->ip?></td>
                <td><?php ipReason($b->reason);?></td>
                <td><?php echouser($b->last_user);?></td>
                <td><input type="checkbox" name="deleteblack[<?=$b->id?>]" value="<?=$b->id?>"></td>
              </tr>
          <?php }?>
            </tbody>
          </table>
        </div>
  </div>
</form>
</div>
</div>

      <!-- End of main content section -->

      <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

      <!-- Place any per-page javascript here -->
      <script src="js/jwerty.js"></script>
      <script>
      jwerty.key('esc', function () {
        $('.modal').modal('hide');
      });
      </script>
      <script src="/users/js/search.js" charset="utf-8"></script>

      <script>
    	$(document).ready(function() {
    		$('#paginate').DataTable(
          {  searching: false,
            "pageLength": 25
          }
        );
    	} );
    	</script>
    	<script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
    	<script src="js/pagination/dataTables.js" type="text/javascript"></script>

      <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>

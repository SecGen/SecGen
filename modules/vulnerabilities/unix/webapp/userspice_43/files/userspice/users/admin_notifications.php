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

Special thanks to user Brandin for the mods!
*/
?>
<?php
require_once 'init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
?>

<?php if (!securePage($_SERVER['PHP_SELF'])){die();}
if($settings->notifications != 1){
  Redirect::to('admin.php?err=Notifications+are+disabled');
}
$validation = new Validate();
$errors = [];
$successes = [];
?>
<?php
if (!empty($_POST)) {
  $action = Input::get('action');
  if ($action=="read" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminNotifications("read",$deletions,$user->data()->id)){
      $successes[] = "Successfully marked $deletion_count notification(s) read.";
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="delete" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminNotifications("delete",$deletions,$user->data()->id)){
      $successes[] = "Successfully deleted $deletion_count notification(s).";
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }

if(!empty($_POST['send_mass_message'])){
  $date = date("Y-m-d H:i:s");
  $msg = Input::get('message',TRUE);

  $userData = fetchMessageUsers(); //Fetch information for all users
        foreach($userData as $v1) {
  $notifications->addNotification($msg,$v1->id);
        logger($user->data()->id,"Notifications - Mass","Sent a notification to $v1->fname.");
      }

  $successes[] = "Your mass notification has been sent!";
  logger($user->data()->id,"Notifications - Mass","Finished sending mass message.");
}
}
$adminNotificationsQ = $db->query("SELECT * FROM notifications ORDER BY date_created DESC");
$adminNotifications = $adminNotificationsQ->results();
$count = $adminNotificationsQ->count();
?>
<div id="page-wrapper">
	<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h1>Notifications Manager</h1>
        </div>
    </div>
    <hr />
		<div class="row">
			<div class="page-wrapper">

<?=resultBlock($errors,$successes);?>
<?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
      <?php if($count > 0) {?><label><input type="checkbox" class="checkAllMsg" />
      [ check/uncheck all ]</label><?php } ?>                         <div class="btn-group pull-right"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#composemass"><i class="glyphicon glyphicon-plus"></i> New Mass Notification</button></div>
      <br><br>
          <form name="threads" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
        <table id="paginate" class="table table-striped">
          <thead>
            <tr>
              <th></th>
              <th></th>
            </tr>
          </thead>
          <tbody>
              <?php if($count > 0) {?>
                <?php foreach($adminNotifications as $m){?>
                  <tr>
                    <td style="width:85px">
    									<span class="chat-img pull-left" style="padding:5px">
    													<img src="<?=get_gravatar($db->query("SELECT email FROM users WHERE id = $m->user_id")->first()->email)?>" width="75" class="img-thumbnail">
    									</span>
                  </td>
                  <td><input type="checkbox" class="maincheck" name="checkbox[<?=$m->id?>]" value="<?=$m->id?>"/> <?=echouser($m->user_id)?>, <?=time2str($m->date_created)?><br /><?=html_entity_decode($m->message)?>
                    <br />
                    <?php if($m->is_read==1 && $m->is_archived==0) {?><i class="glyphicon glyphicon-check"></i> Read<?php } if($m->is_read==0 && $m->is_archived==0) { ?> <i class="glyphicon glyphicon-unchecked"></i> Delivered<?php } if($m->is_archived==1) { ?><i class="glyphicon glyphicon-remove"></i> Deleted<?php } ?>
                  </td>
                </tr>
            <?php  } } else {?>
                        <td colspan="2"><center><h3>No Notifications</h3></center></td></tr>
                        <?php } ?>
              </tbody>
            </table></center>
						<?php if($count > 0) {?>
            <table class="table pull-right" width="20%">
                <tr>
                    <td width="15%">
              <select class="form-control" name="action" required>
                <option value="">Please select an action...</option>
                <option value="read">Mark selected notifications Read</option>
                <option value="delete">Delete selected notifications</option>
              </select>
            </td>
            <td width="5%">
            <input class='btn btn-primary' type='submit' name="admin_notifications" value='Go!' class='submit' /></td></tr></table>
						<?php } ?>
                                </form>
            </div><!-- End of main content section --><br />
          </div> <!-- /.col -->

  <div id="composemass" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">New Mass Notification</h4>
      </div>
      <div class="modal-body">
<form name="create_mass_message" action="?" method="post">

                <label>Content:</label>
                <textarea rows="5" cols="80" class="form-control" name="message"></textarea>
                <input required type="hidden" name="csrf" value="<?=Token::generate();?>" >
              </p>
              <p>
                  <br />
      </div>
      <div class="modal-footer">
          <div class="btn-group">       <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
        <input class='btn btn-primary' type='submit' name="send_mass_message" value='Send Message' class='submit' /></div>
        </form>
         <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
      </div>
    </div>
        </div>
  </div>

        </div> <!-- /.row -->
      </div> <!-- /.container -->
    </div> <!-- /.wrapper -->


    <!-- footers -->
    <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

    <!-- Place any per-page javascript here -->
  <script>
	$(document).ready(function() {
		$('#paginate').DataTable(
      {  searching: true,
        "pageLength": 25,
        "ordering": false,
        "aLengthMenu": [[10, 25, 50, 100,-1], [10, 25, 50,100,"All"]],
      }
    );
    $('.checkAllMsg').on('click', function(e) {
             $('.maincheck').prop('checked', $(e.target).prop('checked'));
     });
	} );
	</script>
	<script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
	<script src="js/pagination/dataTables.js" type="text/javascript"></script>
    <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>

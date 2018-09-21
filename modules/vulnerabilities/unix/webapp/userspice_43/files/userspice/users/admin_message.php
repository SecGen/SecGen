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
if($settings->messaging != 1){
  Redirect::to('admin.php?err=Messaging+is+disabled');
}
?>
<style>
.chat
{
  list-style: none;
  margin: 0;
  padding: 0;
}

.chat li
{
  margin-bottom: 10px;
  padding-bottom: 5px;
  border-bottom: 1px dotted #B3A9A9;
}

.chat li.left .chat-body
{
  margin-left: 60px;
}

.chat li.right .chat-body
{
  margin-right: 60px;
}


.chat li .chat-body p
{
  margin: 0;
  color: #777777;
}

.panel .slidedown .glyphicon, .chat .glyphicon
{
  margin-right: 5px;
}

.panel-body
{
  overflow-y: scroll;
  height: 250px;
}

::-webkit-scrollbar-track
{
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
  background-color: #F5F5F5;
}

::-webkit-scrollbar
{
  width: 12px;
  background-color: #F5F5F5;
}

::-webkit-scrollbar-thumb
{
  -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
  background-color: #555;
}
</style>
<?php
$validation = new Validate();
$errors = [];
$successes = [];
$id = Input::get('id');

$findThread = $db->query("SELECT * FROM message_threads WHERE id = ?",array($id));
$thread = $findThread->first();

$findMessageQ = $db->query("SELECT * FROM messages WHERE msg_thread = ?",array($id));
$messages = $findMessageQ->results();
$single = $findMessageQ->first();

//
$validation = new Validate();
//PHP Goes Here!

$errors = [];
$successes = [];
//PHP Goes Here!

if (!empty($_POST)) {
  if (!empty($_POST['action'])){
    if (!empty($_POST['delete'])){
    $deletions = $_POST['delete'];
    if ($deletion_count = deleteMessages($deletions,1)){
      $successes[] = "Deleted $deletion_count messages.";
    }
    else {
      $errors[] = lang("SQL_ERROR");
    } }
    if (!empty($_POST['undelete'])){
    $deletions = $_POST['undelete'];
    if ($deletion_count = deleteMessages($deletions,0)){
      $successes[] = "Undeleted $deletion_count messages.";
    }
    else {
      $errors[] = lang("SQL_ERROR");
    } }
  }
  $findThread = $db->query("SELECT * FROM message_threads WHERE id = ?",array($id));
  $thread = $findThread->first();

  $findMessageQ = $db->query("SELECT * FROM messages WHERE msg_thread = ?",array($id));
  $messages = $findMessageQ->results();
  $single = $findMessageQ->first();
}
?>
<div id="page-wrapper">
  <div class="container-fluid">
<?=resultBlock($errors,$successes);?>
<?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
    <div class="row">

          <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
      <div class="col-sm-10 col-sm-offset-1">
        <div class="row">
          <div class="col-sm-10">
            <h2><a href="admin_messages.php"><i class="glyphicon glyphicon-chevron-left"></i></a> <?=$thread ->msg_subject?> - ADMIN VIEW</h2>
          </div>
          <div class="col-sm-2">
          </div>
        </div>
        <label><input type="checkbox" class="checkAllMsg" />
        [ check/uncheck all ]</label>
        <form name="messages" action="?id=<?=$id?>" method="post">
        <ul class="chat">
          <?php
          //dnd($messages);$grav = get_gravatar(strtolower(trim($user->data()->email)));
          foreach ($messages as $m){
            $findUser = $db->query("SELECT email FROM users WHERE id = $m->msg_from");
            if($findUser->count()==1) $foundUser = $findUser->first()->email;
            if($findUser->count()==0) $foundUser = "null@null.com";
            $grav = get_gravatar(strtolower(trim($foundUser)));
                        $lastmessage = strtotime($m->sent_on);
                                $difference = ceil((time() - $lastmessage) / (60 * 60 * 24));
                                // if($difference==0) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                                if($difference >= 0 && $difference < 7) {
                                        $today = date("j");
                                        $last_message = date("j",$lastmessage);
                                        if($today==$last_message) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                                        else {
                                $last_update = date("l g:i A",$lastmessage); } }
                                elseif($difference >= 7) { $last_update = date("M j, Y g:i A",$lastmessage); }
            if($m->msg_to == $user->data()->id){
              ?>
              <li class="left clearfix"><span class="chat-img pull-left" style="padding-right:10px">
                <img src="<?=$grav ?>" width="75" class="img-thumbnail" alt="Generic placeholder thumbnail"></p>
                <!-- <img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle" /> -->
              </span>
              <div class="chat-body clearfix">
                <div class="header">
                  <strong class="primary-font"><?php echouser($m->msg_from);?></strong> <small class="pull-right text-muted">
                    <span class="glyphicon glyphicon-time"></span><?=$last_update?></small>
                  </div>
                  <p>
                    <?php $msg = html_entity_decode($m->msg_body);
                    echo $msg; ?>
                  </p>
                  <p class="pull-right"><?php if($m->msg_read==1 && $m->deleted==0) {?><i class="glyphicon glyphicon-check"></i> Read<?php } if($m->msg_read==0 && $m->deleted==0) { ?><i class="glyphicon glyphicon-unchecked"></i> Delivered<?php } if($m->deleted==1) { ?><i class="glyphicon glyphicon-remove"></i> Deleted<?php } ?></p>
                  <?php if($m->deleted==0) {?><br /><label class="pull-right"><input type="checkbox" class="maincheck" name="delete[<?=$m->id?>]" value="<?=$m->id?>"/> Delete?</label><?php } ?>
                  <?php if($m->deleted==1) {?><br /><label class="pull-right"><input type="checkbox" class="maincheck" name="undelete[<?=$m->id?>]" value="<?=$m->id?>"/> Undelete?</label><?php } ?>
                </div>
              </li>

              <?php }else{ ?>

                <li class="left clearfix"><span class="chat-img pull-left" style="padding-right:10px">
                  <img src="<?=$grav; ?>" width="75" class="img-thumbnail" alt="Generic placeholder thumbnail"></p>
                </span>
                <div class="chat-body clearfix">
                  <div class="header">
                    <small class="pull-right text-muted"><span class="glyphicon glyphicon-time"></span><?=$last_update?></small>
                    <strong class="pull-left primary-font"><?php echouser($m->msg_from);?></strong>
                  </div>
                  <p>
                    <br>
                    <?php $msg = html_entity_decode($m->msg_body);
                    echo $msg; ?>
                  </p>
                  <p class="pull-right"><?php if($m->msg_read==1 && $m->deleted==0) {?><i class="glyphicon glyphicon-check"></i> Read<?php } if($m->msg_read==0 && $m->deleted==0) { ?><i class="glyphicon glyphicon-unchecked"></i> Delivered<?php } if($m->deleted==1) { ?><i class="glyphicon glyphicon-remove"></i> Deleted<?php } ?></p>
                  <?php if($m->deleted==0) {?><br /><label class="pull-right"><input type="checkbox" class="maincheck" name="delete[<?=$m->id?>]" value="<?=$m->id?>"/> Delete?</label><?php } ?>
                  <?php if($m->deleted==1) {?><br /><label class="pull-right"><input type="checkbox" class="maincheck" name="undelete[<?=$m->id?>]" value="<?=$m->id?>"/> Undelete?</label><?php } ?>
                </div>
              </li>



              <?php } //end if/else statement ?>


              <?php } //end foreach ?>

              <ul>
                <!-- <h3>From: <?php //echouser($m->msg_from);?></h3> -->

                <input class='btn btn-primary pull-right' type='submit' name="action" value='Take Selected Actions' class='submit' /></div><br /></form>
              </div> <!-- /.col --><br />
              </div> <!-- /.row -->
            </div> <!-- /.container -->
          </div> <!-- /.wrapper -->


          <!-- footers -->
          <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>
            <script src='https:////cdn.tinymce.com/4/tinymce.min.js'></script>
                        <script src="js/jwerty.js"></script>
                        <script>
                        tinymce.init({
                        selector: '#mytextarea'
                        });
                        $('.checkAllMsg').on('click', function(e) {
                                 $('.maincheck').prop('checked', $(e.target).prop('checked'));
                         });
                        jwerty.key('esc', function () {
                                $('.modal').modal('hide');
                        });
                        jwerty.key('shift+r', function () {
                                $('.modal').modal('hide');
                                $('#reply').modal();
                        });
                        jwerty.key('alt+r', function () {
                                $('.modal').modal('hide');
                                $('#msg_body').focus();
                        });
                        </script>
            <!-- Place any per-page javascript here -->

            <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>

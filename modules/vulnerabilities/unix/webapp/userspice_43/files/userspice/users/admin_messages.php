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
$validation = new Validate();
$errors = [];
$successes = [];
?>
<?php
if (!empty($_POST)) {
  $token = $_POST['csrf'];
  if(!Token::check($token)){
    include('../usersc/scripts/token_error.php');
  }
  $action = Input::get('action');
  if ($action=="archive" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminArchiveThread($deletions,"both",$user->data()->id)){
      $successes[] = lang("MESSAGE_ARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="archiveto" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminArchiveThread($deletions,"msg_to",$user->data()->id)){
      $successes[] = lang("MESSAGE_ARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="archivefrom" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminArchiveThread($deletions,"msg_from",$user->data()->id)){
      $successes[] = lang("MESSAGE_ARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="unarchive" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminUnarchiveThread($deletions,"both",$user->data()->id)){
      $successes[] = lang("MESSAGE_UNARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="unarchiveto" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminUnarchiveThread($deletions,"msg_to",$user->data()->id)){
      $successes[] = lang("MESSAGE_UNARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="unarchivefrom" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminUnarchiveThread($deletions,"msg_from",$user->data()->id)){
      $successes[] = lang("MESSAGE_UNARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="delete" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminDeleteThread($deletions,"both",$user->data()->id)){
      $successes[] = lang("MESSAGE_DELETE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="deleteto" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminDeleteThread($deletions,"msg_to",$user->data()->id)){
      $successes[] = lang("MESSAGE_DELETE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if ($action=="deletefrom" && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = adminDeleteThread($deletions,"msg_from",$user->data()->id)){
      $successes[] = lang("MESSAGE_DELETE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }

  if(!empty($_POST['messageSettings'])) {
    if($settings->msg_notification != $_POST['msg_notification']) {
      $msg_notification = Input::get('msg_notification');
      if(empty($msg_notification)) { $msg_notification==0; }
      $fields=array('msg_notification'=>$msg_notification);
      $db->update('settings',1,$fields);
      $successes[] = "Set msg_notification to $msg_notification";
      logger($user->data()->id,"Setting Change","Changed msg_notification from $settings->msg_notification to $msg_notification.");
    }

    if($settings->msg_blocked_users != $_POST['msg_blocked_users']) {
      $msg_blocked_users = Input::get('msg_blocked_users');
      if(empty($msg_blocked_users)) { $msg_blocked_users==0; }
      $fields=array('msg_blocked_users'=>$msg_blocked_users);
      $db->update('settings',1,$fields);
      $successes[] = "Set msg_blocked_users to $msg_blocked_users";
      logger($user->data()->id,"Setting Change","Changed msg_blocked_users from $settings->msg_blocked_users to $msg_blocked_users.");
    }

    if($settings->msg_default_to != $_POST['msg_default_to']) {
      $msg_default_to = Input::get('msg_default_to');
      if(empty($msg_default_to)) { $msg_default_to==0; }
      $fields=array('msg_default_to'=>$msg_default_to);
      $db->update('settings',1,$fields);
      $successes[] = "Set msg_default_to to $msg_default_to";
      logger($user->data()->id,"Setting Change","Changed msg_default_to from $settings->msg_default_to to $msg_default_to.");
    }
    $settingsQ = $db->query("SELECT * FROM settings");
    $settings = $settingsQ->first();
  }

  if(!empty($_POST['send_mass_message'])){
    $date = date("Y-m-d H:i:s");
    $msg_subject = Input::get('msg_subject');
    $sendEmail = Input::get('sendEmail');

    $userData = fetchMessageUsers(); //Fetch information for all users
    foreach($userData as $v1) {
      $thread = array(
        'msg_from'    => $user->data()->id,
        'msg_to'      => $v1->id,
        'msg_subject' => Input::get('msg_subject'),
        'last_update' => $date,
        'last_update_by' => $user->data()->id,
      );
      $db->insert('message_threads',$thread);
      $newThread = $db->lastId();


      $fields = array(
        'msg_from'    => $user->data()->id,
        'msg_to'      => $v1->id,
        'msg_body'    => Input::get('msg_body'),
        'msg_thread'  => $newThread,
        'sent_on'     => $date,
      );

      $db->insert('messages',$fields);
      if(isset($_POST['sendEmail'])) {
        $email = $db->query("SELECT fname,email,msg_notification FROM users WHERE id = ?",array($v1->id))->first();
        if($settings->msg_notification == 1 && $v1->msg_notification == 1 && isset($_POST['sendEmail'])) {
          $params = array(
            'fname' => $user->data()->fname,
            'sendfname' => $v1->fname,
            'body' => Input::get('msg_body'),
            'msg_thread' => $newThread,
          );
          $to = rawurlencode($email->email);
          $body = email_body('_email_msg_template.php',$params);
          email($to,$msg_subject,$body);
          logger($user->data()->id,"Messaging - Mass","Sent a message to $email->fname.");
        } } }

        $successes[] = "Your mass message has been sent!";
        logger($user->data()->id,"Messaging - Mass","Finished sending mass message.");
      } }
      $messagesQ = $db->query("SELECT * FROM message_threads ORDER BY last_update DESC");
      $messages = $messagesQ->results();
      $count = $messagesQ->count();
      $csrf = Token::generate();
      ?>
      <div id="page-wrapper">

        <div class="container">

          <?=resultBlock($errors,$successes);?>
          <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>


          <div class="row">
            <div class="col-sm-12">
              <div class="row" id="maindiv">
                <div>
                  <h1><center>Conversations <a href="#" data-toggle="modal" class="nounderline" data-target="#settings"><i class="glyphicon glyphicon-cog"></i></a></center></h1>
                </div>
                <?php if($count > 0) {?><label><input type="checkbox" class="checkAllMsg" />
                  [ check/uncheck all ]</label><?php } ?>                         <div class="btn-group pull-right"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#composemass"><i class="glyphicon glyphicon-plus"></i> New Mass Message</button></div>
                  <form name="threads" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                    <center><table id="paginate" class="table table-striped">
                      <thead>
                        <tr>
                          <th></th>
                          <th></th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <?php if($count > 0) {?>
                            <?php foreach($messages as $m){
                              if($m->msg_from == $user->data()->id) { $toId = $m->msg_to; $fromId = $m->msg_from; } else { $toId = $m->msg_from; $fromId = $m->msg_to; }
                              $fromQ = $db->query("SELECT picture,email FROM users WHERE id = $fromId");
                              if($fromQ->count()==1) $fromUser = $fromQ->first()->email;
                              if($fromQ->count()==0) $fromUser = "null@null.com";
                              $fromGrav = get_gravatar(strtolower(trim($fromUser)));
                              $toQ = $db->query("SELECT picture,email FROM users WHERE id = $toId");
                              if($toQ->count()==1) $toUser = $toQ->first()->email;
                              if($toQ->count()==0) $toUser = "null@null.com";
                              $toGrav = get_gravatar(strtolower(trim($toUser)));
                              $lastmessage = strtotime($m->last_update);
                              $difference = ceil((time() - $lastmessage) / (60 * 60 * 24));
                              // if($difference==0) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                              if($difference >= 0 && $difference < 7) {
                                $today = date("j");
                                $last_message = date("j",$lastmessage);
                                if($today==$last_message) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                                else {
                                  $last_update = date("l g:i A",$lastmessage); } }
                                  elseif($difference >= 7) { $last_update = date("M j, Y g:i A",$lastmessage); }
                                  $replies = $db->query("SELECT COUNT(*) AS count FROM messages WHERE msg_thread = ? GROUP BY msg_thread",array($m->id));
                                  $repliescount = $replies->count();
                                  ?>
                                  <td style="width:200px">
                                    <span class="chat-img pull-left" style="padding-left:5px">
                                      <a class="nounderline" href="admin_message.php?id=<?=$m->id?>">
                                        <img src="<?=$fromGrav ?>" width="75" class="img-thumbnail">
                                      </a>
                                    </span>
                                    <span class="chat-img pull-right" style="padding-right:5px">
                                      <a class="nounderline" href="admin_message.php?id=<?=$m->id?>">
                                        <img src="<?=$toGrav ?>" width="75" class="img-thumbnail">
                                      </a>
                                    </span>

                                  </td>
                                  <td class="pull-left">
                                    <h4>
                                      <input type="checkbox" class="maincheck" name="checkbox[<?=$m->id?>]" value="<?=$m->id?>"/>
                                      <a class="nounderline" href="admin_message.php?id=<?=$m->id?>">
                                        <?=$m->msg_subject?> - between <?=echouser($m->msg_from);?> and <?=echouser($m->msg_to);?>
                                      </a>
                                      <?php $unread = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND msg_to = ? AND msg_read = ?",array($m->id,$user->data()->id,0));
                                      $unreadCount = $unread->count();?></h4>
                                      <a class="nounderline" href="admin_message.php?id=<?=$m->id?>">
                                        Updated <?=$last_update?> by <?php echouser($m->last_update_by);?>
                                      </a>
                                    </td>
                                    <td>
                                      <span class="pull-left"><i class="glyphicon glyphicon-<?php if($m->archive_from==0) {?>unchecked<?php } else {?>check<?php } ?>"></i> Archived by <?=echouser($m->msg_from)?> (f)</span>
                                      <span class="pull-right"><i class="glyphicon glyphicon-<?php if($m->archive_to==0) {?>unchecked<?php } else {?>check<?php } ?>"></i> Archived by <?=echouser($m->msg_to)?> (t)</span><br /><hr>
                                      <span class="pull-left"><i class="glyphicon glyphicon-<?php if($m->hidden_from==0) {?>unchecked<?php } else {?>check<?php } ?>"></i> Deleted by <?=echouser($m->msg_from)?> (f)</span>
                                      <span class="pull-right"><i class="glyphicon glyphicon-<?php if($m->hidden_to==0) {?>unchecked<?php } else {?>check<?php } ?>"></i> Deleted by <?=echouser($m->msg_to)?> (t)</span>
                                    </td>
                                  </tr>
                                <?php } } else {?>
                                  <td colspan="2"><center><h3>No Conversations</h3></center></td></tr>
                                <?php } ?>
                              </tbody>
                            </table></center>
                            <?php if($count > 0) {?>
                              <table class="table pull-right" width="20%">
                                <tr>
                                  <td width="15%">
                                    <select class="form-control" name="action" required>
                                      <option value="">Please select an action...</option>
                                      <option value="archiveto">Archive To Selected Threads</option>
                                      <option value="archivefrom">Archive From Selected Threads</option>
                                      <option value="archive">Archive To+From Selected Threads</option>
                                      <option value="unarchiveto">Undelete+Unarchive To Selected Threads</option>
                                      <option value="unarchivefrom">Undelete+Unarchive From Selected Threads</option>
                                      <option value="unarchive">Undelete+Unarchive To+From Selected Threads</option>
                                      <option value="deleteto">Delete+Archive To Selected Threads</option>
                                      <option value="deletefrom">Delete+Archive From Selected Threads</option>
                                      <option value="delete">Delete+Archive To+From Selected Threads</option>
                                    </select>
                                  </td>
                                  <td width="5%">
                                    <input type="hidden" name="csrf" value="<?=$csrf;?>" />
                                    <input class='btn btn-primary' type='submit' name="admin_messages" value='Go!' class='submit' /></td></tr></table>
                                  <?php } ?>
                                </form>
                              </div><!-- End of main content section --><br />
                            </div> <!-- /.col -->

                            <div id="settings" class="modal fade" role="dialog">
                              <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">Message Settings</h4>
                                  </div>
                                  <div class="modal-body">
                                    <form class="form" id="messageSettings" name='messageSettings' action='admin_messages.php' method='post'>
                                      <div class="form-group">
                                        <label for="msg_notification">Message Email Notification</label>
                                        <select id="msg_notification" class="form-control" name="msg_notification">
                                          <option value="1" <?php if($settings->msg_notification==1) echo 'selected="selected"'; ?> >Enabled</option>
                                          <option value="0" <?php if($settings->msg_notification==0) echo 'selected="selected"'; ?> >Disabled</option>
                                        </select>
                                      </div>

                                      <div class="form-group">
                                        <label for="msg_blocked_users">Allow Messages to Blocked Users?</label>
                                        <select id="msg_blocked_users" class="form-control" name="msg_blocked_users">
                                          <option value="1" <?php if($settings->msg_blocked_users==1) echo 'selected="selected"'; ?> >Enabled</option>
                                          <option value="0" <?php if($settings->msg_blocked_users==0) echo 'selected="selected"'; ?> >Disabled</option>
                                        </select>
                                      </div>

                                      <div class="form-group">
                                        <label for="msg_default_to">Disable replying to system messages (messageUser function)?</label>
                                        <select id="msg_default_to" class="form-control" name="msg_default_to">
                                          <option value="1" <?php if($settings->msg_default_to==1) echo 'selected="selected"'; ?> >Yes</option>
                                          <option value="0" <?php if($settings->msg_default_to==0) echo 'selected="selected"'; ?> >No</option>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="modal-footer">
                                      <div class="btn-group">
                                        <input type="hidden" name="csrf" value="<?=$csrf;?>" />
                                        <input class='btn btn-primary' type='submit' name="messageSettings" value='Update' class='submit' /></div>
                                      </form>
                                      <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                                    </div>
                                  </div>

                                </div>
                              </div>

                              <div id="composemass" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                  <!-- Modal content-->
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                                      <h4 class="modal-title">New Mass Message</h4>
                                    </div>
                                    <div class="modal-body">
                                      <form name="create_mass_message" action="admin_messages.php" method="post">

                                        <label>Subject:</label>
                                        <input required size='100' class='form-control' type='text' name='msg_subject' value='' required/>
                                        <br /><label>Body:</label>
                                        <textarea rows="20" cols="80"  id="mytextarea2" name="msg_body"></textarea>
                                        <label><input type="checkbox" name="sendEmail" id="sendEmail" checked /> Send Email Notification if Enabled?</label>
                                        <input type="hidden" name="csrf" value="<?=$csrf;?>" />
                                      </p>
                                      <p>
                                        <br />
                                      </div>
                                      <div class="modal-footer">
                                        <div class="btn-group">
                                          <input type="hidden" name="csrf" value="<?=$csrf;?>" />
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
                          <script src='https://cdn.tinymce.com/4/tinymce.min.js'></script>
                          <script src="../usersc/scripts/jwerty.js"></script>
                          <script src="../usersc/scripts/combobox.js"></script>
                          <script>
                          $(document).ready(function(){
                            $('.combobox').combobox();
                          });
                          tinymce.init({
                            selector: '#mytextarea'
                          });
                          tinymce.init({
                            selector: '#mytextarea2'
                          });
                          $('.checkAllMsg').on('click', function(e) {
                            $('.maincheck').prop('checked', $(e.target).prop('checked'));
                          });
                          $('.checkAllArchive').on('click', function(e) {
                            $('.checkarchive').prop('checked', $(e.target).prop('checked'));
                          });
                          jwerty.key('esc', function () {
                            $('.modal').modal('hide');
                          });
                          </script>
                          <script>
                          $(document).ready(function() {
                            $('#paginate').DataTable(
                              {  searching: false,
                                "pageLength": 10
                              }
                            );
                          } );
                          </script>
                          <script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
                          <script src="js/pagination/dataTables.js" type="text/javascript"></script>
                          <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>

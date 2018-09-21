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
  Redirect::to('account.php?err=Messaging+is+disabled');
}
$validation = new Validate();
$errors = [];
$successes = [];
?>
<?php
if (!empty($_POST)) {
  //Delete User Checkboxes
  if (!empty($_POST['archive'])){
    $deletions = $_POST['archive'];
    if ($deletion_count = archiveThreads($deletions,$user->data()->id,1)){
      $successes[] = lang("MESSAGE_ARCHIVE_SUCCESSFUL", array($deletion_count));
      Redirect::to('messages.php');
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if (!empty($_POST['unarchive']) && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = archiveThreads($deletions,$user->data()->id,0)){
      $successes[] = lang("MESSAGE_UNARCHIVE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if (!empty($_POST['delete']) && isset($_POST['checkbox'])){
    $deletions = $_POST['checkbox'];
    if ($deletion_count = deleteThread($deletions,$user->data()->id,1)){
      $successes[] = lang("MESSAGE_DELETE_SUCCESSFUL", array($deletion_count));
    }
    else {
      $errors[] = lang("SQL_ERROR");
    }
  }
  if(!empty($_POST['send_message'])){

    if (empty(Input::get('user_id'))) {
      $errors[] = "Unknown recipient"; }

      if (strlen(Input::get('msg_body')) == 0) {
        $errors[] = "Message cannot be empty"; }

        $date = date("Y-m-d H:i:s");

        $thread = array(
          'msg_from'    => $user->data()->id,
          'msg_to'      => Input::get('user_id'),
          'msg_subject' => Input::get('msg_subject'),
          'last_update' => $date,
          'last_update_by' => $user->data()->id,
        );
        if (empty($errors)) {
          $db->insert('message_threads',$thread); }
          $newThread = $db->lastId();


          $fields = array(
            'msg_from'    => $user->data()->id,
            'msg_to'      => Input::get('user_id'),
            'msg_body'    => Input::get('msg_body'),
            'msg_thread'  => $newThread,
            'sent_on'     => $date,
          );
          $msgto = Input::get('user_id');
          $msg_subject = Input::get('msg_subject');

          if (empty($errors)) {
            $db->insert('messages',$fields);
            $email = $db->query("SELECT fname,email,msg_notification FROM users WHERE id = ?",array($msgto))->first();
            if($settings->msg_notification == 1 && $email->msg_notification == 1) {
              $params = array(
                'fname' => $user->data()->fname,
                'sendfname' => $email->fname,
                'body' => Input::get('msg_body'),
                'msg_thread' => $newThread,
              );
              $to = rawurlencode($email->email);
              $body = email_body('_email_msg_template.php',$params);
              email($to,$msg_subject,$body);
              logger($user->data()->id,"Messaging","Sent a message to $email->fname.");
            } }

            $successes[] = "Your message has been sent!"; }
          }

          if(!empty($_POST['messageSettings'])) {
            //Toggle msg_notification setting
            if($settings->msg_notification==1) {
              $msg_notification = Input::get("msg_notification");
              if (isset($msg_notification) AND $msg_notification == 'Yes'){
                if ($user->data()->msg_notification == 0){
                  if (updateUser('msg_notification', $userId, 1)){
                    $successes[] = lang("FRONTEND_USER_SYS_TOGGLED", array("msg_notification","enabled"));
                  }else{
                    $errors[] = lang("SQL_ERROR");
                  }
                }
              }elseif ($user->data()->msg_notification == 1){
                if (updateUser('msg_notification', $userId, 0)){
                  $successes[] = lang("FRONTEND_USER_SYS_TOGGLED", array("msg_notification","disabled"));
                }else{
                  $errors[] = lang("SQL_ERROR");
                }
              } }
            }

            if(!empty($_POST['send_mass_message'])){
              $date = date("Y-m-d H:i:s");
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
                  }
                  logger($user->data()->id,"Messaging - Mass","Sent a message to $email->fname.");
                }

                $successes[] = "Your mass message has been sent!";
                logger($user->data()->id,"Messaging - Mass","Finished sending mass message.");
              } }
              $messagesQ = $db->query("SELECT * FROM message_threads WHERE (msg_to = ? AND archive_to = ? AND hidden_to = ?) OR (msg_from = ? AND archive_from = ? AND hidden_from = ?) ORDER BY last_update DESC",array($user->data()->id,0,0,$user->data()->id,0,0));
              $messages = $messagesQ->results();
              $count = $messagesQ->count();
              $archiveCount = $db->query("SELECT * FROM message_threads WHERE (msg_to = ? AND archive_to = ? AND hidden_to = ?) OR (msg_from = ? AND archive_from = ? AND hidden_from = ?) ORDER BY last_update DESC",array($user->data()->id,1,0,$user->data()->id,1,0))->count();

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
                          <h1><?php if (checkMenu(2,$user->data()->id)){  ?><div class="btn-group pull-left"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#composemass"><i class="glyphicon glyphicon-plus"></i> New Mass Message</button></div><?php } ?> <center>Conversations <a href="#" data-toggle="modal" class="nounderline" data-target="#settings"><i class="glyphicon glyphicon-cog"></i></a> <div class="btn-group pull-right"><button type="button" class="btn btn-info" data-toggle="modal" data-target="#compose"><i class="glyphicon glyphicon-plus"></i> New Message</button></div></center></h1>
                        </div>
                        <?php if($count > 0) {?><label><input type="checkbox" class="checkAllMsg" />
                          [ check/uncheck all ]</label><?php } ?>
                          <form name="threads" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                            <center><table id="paginate" class="table table-striped">
                              <thead>
                                <tr>
                                  <th></th>
                                  <th></th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <?php if($count > 0) {?>
                                    <?php foreach($messages as $m){
                                      if($m->msg_from == $user->data()->id) { $findId = $m->msg_to; } else { $findId = $m->msg_from; }
                                      $findUser = $db->query("SELECT picture,email FROM users WHERE id = $findId");
                                      if($findUser->count()==1) $foundUser = $findUser->first()->email;
                                      if($findUser->count()==0) $foundUser = "null@null.com";
                                      $grav = get_gravatar(strtolower(trim($foundUser))); ?>
                                      <?php $lastmessage = strtotime($m->last_update);
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
                                          <td style="width:100px">
                                            <center>
                                              <span class="chat-img pull-left" style="padding-right:5px">
                                                <a class="nounderline" href="message.php?id=<?=$m->id?>">
                                                  <img src="<?=$grav ?>" width="75" class="img-thumbnail">
                                                </a>
                                              </span>
                                            </center>
                                          </td>
                                          <td class="pull-left">
                                            <h4>
                                              <input type="checkbox" class="maincheck" name="archive[<?=$m->id?>]" value="<?=$m->id?>"/>
                                              <a class="nounderline" href="message.php?id=<?=$m->id?>">
                                                <?=$m->msg_subject?> - with <?php if($m->msg_from == $user->data()->id) { echouser($m->msg_to); } else { echouser($m->msg_from); } ?>
                                              </a>
                                              <?php $unread = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND msg_to = ? AND msg_read = ?",array($m->id,$user->data()->id,0));
                                              $unreadCount = $unread->count();?>
                                              <?php if($unreadCount > 0) {?> - <font color="red"><?=$unreadCount?> New Message<?php if($unreadCount > 1) {?>s<?php } ?></font><?php } ?></h4>
                                              <a class="nounderline" href="message.php?id=<?=$m->id?>">
                                                Updated <?=$last_update?> by <?php echouser($m->last_update_by);?>
                                              </a>
                                            </tr>
                                          <?php } } else {?>
                                            <td colspan="2"><center><h3>No Conversations</h3></center></td></tr>
                                          <?php } ?>
                                        </tbody>
                                      </table></center>
                                      <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                      <?php if($count > 0) {?><div class="btn-group pull-right"><input class='btn btn-danger' type='submit' name='Submit' value='Archive Selected Threads' /></div><?php } ?>
                                    </form>
                                    <br /><?php if($archiveCount > 0) {?><center><a href="#" data-toggle="modal" data-target="#archived">View Archived Threads</a></center><?php } ?>
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
                                        <form class="form" id="messageSettings" name='messageSettings' action='messages.php' method='post'>
                                          <p><strong>Enable/Disable Functions</strong></p>
                                          <center>
                                            <div class="checkbox <?php if($settings->msg_notification==0) {?> disabled<?php } ?>">
                                              <label>
                                                <input type="checkbox" <?php if($settings->msg_notification==0) {?> disabled<?php } ?> data-toggle="toggle" data-onstyle="info" data-offstyle="danger" name="msg_notification" id="msg_notification" <?php if($user->data()->msg_notification==1) {?>checked <?php } ?> value="Yes">
                                                Message Email Notifications
                                              </label>
                                            </div>
                                          </div>
                                          <div class="modal-footer">
                                            <div class="btn-group">
                                              <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                              <input class='btn btn-primary' type='submit' name="messageSettings" value='Update' class='submit' /></div>
                                            </form>
                                            <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                                          </div>
                                        </div>

                                      </div>
                                    </div>

                                    <div id="compose" class="modal fade" role="dialog">
                                      <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">New Message</h4>
                                          </div>
                                          <div class="modal-body">
                                            <form name="create_message" action="messages.php" method="post">

                                              <label>Select a user:</label>
                                              <select name="user_id" id="combobox" class="form-control combobox" required>
                                                <option readonly></option>
                                                <?php $userData = fetchMessageUsers(); //Fetch information for all users
                                                foreach($userData as $v1) {?>
                                                  <option value="<?=$v1->id;?>"><?=$v1->fname;?> <?=$v1->lname;?></option>
                                                <?php } ?>
                                              </select><br />
                                              <label>Subject:</label>
                                              <input required size='100' class='form-control' type='text' name='msg_subject' value='' required/>
                                              <br /><label>Body:</label>
                                              <textarea rows="20" cols="80"  id="mytextarea" name="msg_body"></textarea>
                                              <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                            </p>
                                            <p>
                                              <br />
                                            </div>
                                            <div class="modal-footer">
                                              <div class="btn-group">
                                                <input class='btn btn-primary' type='submit' name="send_message" value='Send Message' class='submit' /></div>
                                              </form>
                                              <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                                            </div>
                                          </div>
                                        </div>
                                      </div>

                                      <div id="archived" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                          <!-- Modal content-->
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              <h4 class="modal-title">Archived Threads</h4>
                                            </div>
                                            <div class="modal-body" id="archivediv">
                                              <?php $messagesQ2 = $db->query("SELECT * FROM message_threads WHERE (msg_to = ? AND archive_to = ? AND hidden_to = ?) OR (msg_from = ? AND archive_from = ? AND hidden_from = ?) ORDER BY last_update DESC",array($user->data()->id,1,0,$user->data()->id,1,0));
                                              $messages2 = $messagesQ2->results();
                                              $messagesCount2 = $messagesQ2->count(); ?>
                                              <?php if($messagesCount2 > 0) {?><label><input type="checkbox" class="checkAllArchive" />
                                                [ check/uncheck all ]</label><?php } ?>
                                                <form name="uthreads" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
                                                  <center><table class="table table-striped">
                                                    <thead>
                                                      <tr>
                                                        <th></th>
                                                        <th></th>
                                                      </tr>
                                                    </thead>
                                                    <tbody>
                                                      <tr>
                                                        <?php if($messagesCount2 > 0) {?>
                                                          <?php foreach($messages2 as $m2){ ?>
                                                            <?php
                                                            if($m2->msg_from == $user->data()->id) { $findId = $m2->msg_to; } else { $findId = $m2->msg_from; }
                                                            $findUser = $db->query("SELECT picture,email FROM users WHERE id = $findId");
                                                            if($findUser->count()==1) $foundUser = $findUser->first()->email;
                                                            if($findUser->count()==0) $foundUser = "null@null.com";
                                                            $grav = get_gravatar(strtolower(trim($foundUser))); ?>
                                                            <?php $lastmessage = strtotime($m2->last_update);
                                                            $difference = ceil((time() - $lastmessage) / (60 * 60 * 24));
                                                            // if($difference==0) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                                                            if($difference >= 0 && $difference < 7) {
                                                              $today = date("j");
                                                              $last_message = date("j",$lastmessage);
                                                              if($today==$last_message) { $last_update = "Today, "; $last_update .= date("g:i A",$lastmessage); }
                                                              else {
                                                                $last_update = date("l g:i A",$lastmessage); } }
                                                                elseif($difference >= 7) { $last_update = date("M j, Y g:i A",$lastmessage); }
                                                                $replies = $db->query("SELECT COUNT(*) AS count FROM messages WHERE msg_thread = ? GROUP BY msg_thread",array($m2->id));
                                                                $repliescount = $replies->count();
                                                                ?>
                                                                <td style="width:100px">
                                                                  <center>
                                                                    <span class="chat-img pull-left" style="padding-right:5px">
                                                                      <a class="nounderline" href="message.php?id=<?=$m2->id?>">
                                                                        <img src="<?=$grav ?>" width="75" class="img-thumbnail">
                                                                      </a>
                                                                    </span>
                                                                  </center>
                                                                </td>
                                                                <td class="pull-left">
                                                                  <h4>
                                                                    <input type="checkbox" class="checkarchive" name="checkbox[<?=$m2->id?>]" value="<?=$m2->id?>"/>
                                                                    <a class="nounderline" href="message.php?id=<?=$m2->id?>">
                                                                      <?=$m2->msg_subject?> - with <?php if($m2->msg_from == $user->data()->id) { echouser($m2->msg_to); } else { echouser($m2->msg_from); } ?>
                                                                    </a>
                                                                  </h4>
                                                                  <a class="nounderline" href="message.php?id=<?=$m2->id?>">
                                                                    Updated <?=$last_update?> by <?php echouser($m2->last_update_by);?>
                                                                  </a>
                                                                </tr>
                                                              <?php } } else {?>
                                                                <td colspan="2"><center><h3>No Archived Conversations</h3></center></td></tr>
                                                              <?php } ?>
                                                            </tbody>
                                                          </table></center>
                                                          <br />
                                                        </div>
                                                        <div class="modal-footer">
                                                          <div class="btn-group">
                                                            <input type="hidden" name="csrf" value="<?=$csrf?>" />
                                                            <input class='btn btn-primary' type='submit' name="delete" value='Delete Selected Threads' class='submit' /></div>
                                                            <div class="btn-group"><input class='btn btn-primary' type='submit' name="unarchive" value='Unarchive Selected Threads' class='submit' /></div>
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
                                                          <form name="create_mass_message" action="messages.php" method="post">

                                                            <label>Subject:</label>
                                                            <input required size='100' class='form-control' type='text' name='msg_subject' value='' required/>
                                                            <br /><label>Body:</label>
                                                            <textarea rows="20" cols="80"  id="mytextarea2" name="msg_body"></textarea>
                                                            <label><input type="checkbox" name="sendEmail" id="sendEmail" checked /> Send Email Notification if Enabled?</label>
                                                            <input type="hidden" name="csrf" value="<?=$csrf?>" />
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

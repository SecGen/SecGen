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
$unread = Input::get('unread');


$findThread = $db->query("SELECT * FROM message_threads WHERE id = ?",array($id));
$thread = $findThread->first();

$findMessageQ = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND deleted = 0",array($id));
$messages = $findMessageQ->results();
$single = $findMessageQ->first();

$findUnread = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND msg_to = ? AND msg_read != 1 AND deleted = 0",array($id, $user->data()->id));
$myUnread = $findUnread->count();

//make sure there are messages TO me in the thread so I don't get a false unread button
$checkToQ = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND msg_to = ? AND deleted = 0",array($id, $user->data()->id));
$checkTo = $checkToQ->count();

$perm = $db->query("SELECT SUM(permissions) AS count FROM users WHERE id = ? OR id = ?",array($thread->msg_to,$thread->msg_from))->first()->count;
if($perm < 2 && $settings->msg_blocked_users==0) $errors[] = "User is banned, you cannot reply.";
if($thread->hidden_from==1 || $thread->hidden_to==1) $errors[] = "The other user deleted this thread, so you cannot reply.";

if (($single->msg_to != $user->data()->id) && ($single->msg_from != $user->data()->id)){
  $ip = ipCheck();
  $fields = array(
    'user'              => $user->data()->id,
    'page'              => 42,
    'ip'                        => $ip,
  );
  $db->insert('audit',$fields);
  Redirect::to('messages.php?err=That thread does not belong to you or does not exist.'); die();
}

//ONLY mark messages read if you are the recipient
if($unread != 1){
  foreach ($messages as $message){
    if(($message->msg_read == 0) && ($message->msg_to == $user->data()->id)) {
      $db->update('messages',$message->id,['msg_read'=>1]);
    }
  }
}
if(!empty($_POST)){


  if(!empty($_POST['markUnread'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include('../usersc/scripts/token_error.php');
    }
    foreach ($messages as $message){
      if(($message->msg_read == 1) && ($message->msg_to == $user->data()->id)) {
        $db->update('messages',$message->id,['msg_read'=>0]);
        Redirect::to('message.php?id='.$id.'&unread=1');
      }
    }

  }

  if(!empty($_POST['markRead'])){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include('../usersc/scripts/token_error.php');
    }
    foreach ($messages as $message){
      if(($message->msg_read == 0) && ($message->msg_to == $user->data()->id)) {
        $db->update('messages',$message->id,['msg_read'=>1]);
      }
    }
    Redirect::to('message.php?id='.$id);
  }
  //
  $validation = new Validate();

  if(!empty($_POST['reply']) && (($settings->msg_blocked_users==1 || ($perm==2 && $settings->msg_blocked_users==0)) && (!$thread->hidden_from==1 && !$thread->hidden_to==1))){
    $token = $_POST['csrf'];
    if(!Token::check($token)){
      include('../usersc/scripts/token_error.php');
    }

    $to = $single->msg_to;
    if($to == $user->data()->id){
      $to = $single->msg_from;
    }
    $msg_body = Input::get('msg_body');
    $validation->check($_POST,array(
      'msg_body' => array(
        'display' => 'Message',
        'required' => true
      )
    ));
    if($validation->passed()){
      $date = date("Y-m-d H:i:s");
      $fields = array(
        'msg_from'    => $user->data()->id,
        'msg_to'      => $to,
        'msg_body'    => $msg_body,
        'msg_thread'  => $id,
        'sent_on'     => $date,
      );

      $db->insert('messages',$fields);

      $threadUpdate = array(
        'last_update'    => $date,
        'last_update_by' => $user->data()->id,
        'archive_to' => 0,
        'archive_from' => 0
      );

      $db->update('message_threads',$id,$threadUpdate);

      $email = $db->query("SELECT fname,email,msg_notification FROM users WHERE id = ?",array($to))->first();
      if($settings->msg_notification == 1 && $email->msg_notification == 1) {
        $params = array(
          'fname' => $email->fname,
          'sendfname' => $user->data()->fname,
          'body' => Input::get('msg_body'),
          'msg_thread' => $id,
        );
        $to = rawurlencode($email->email);
        $body = email_body('_email_msg_template.php',$params);
        email($to,$thread->msg_subject,$body);
      }
      logger($user->data()->id,"Messaging","Sent a message to $email->fname.");
      $successes[] = "Your message has been sent!";
    }
    $findMessageQ = $db->query("SELECT * FROM messages WHERE msg_thread = ? AND deleted = 0",array($id));
    $messages = $findMessageQ->results();
    $single = $findMessageQ->first();
  }
}
$csrf = Token::generate();
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
            <h2><a href="messages.php"><i class="glyphicon glyphicon-chevron-left"></i></a> <?=$thread ->msg_subject?></h2>
          </div>
          <div class="col-sm-2">
            <?php
            if($myUnread == 0 && $checkTo > 0){
              ?>
              <form class="" action="message.php?id=<?php echo $id?>" method="post">
                <input type="hidden" name="csrf" value="<?=$csrf?>" />
                <input type="submit" class="btn btn-danger" name="markUnread" value="Mark as Unread">
              </form>
              <?php
            }
            ?>
          </div>
        </div>

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
                    <?php if($m->msgfrom = $user->data()->id) {?><p class="pull-right"><?php if($m->msg_read==1) {?><i class="glyphicon glyphicon-check"></i> Read<?php } else { ?><i class="glyphicon glyphicon-unchecked"></i> Delivered<?php } ?></p><?php } ?>
                  </div>
                </li>



              <?php } //end if/else statement ?>


            <?php } //end foreach ?>

            <ul>
              <!-- <h3>From: <?php //echouser($m->msg_from);?></h3> -->

              <h3>Quick Reply <a href="#" data-toggle="modal" data-target="#reply"><i class="glyphicon glyphicon-new-window"></i></a></h3>
              <form name="reply_form" action="message.php?id=<?=$id?>" method="post">
                <div align="center">
                  <input type="text" class="form-control" placeholder="Click here or press Alt + R to focus on this box OR press Shift + R to open the expanded reply pane!" name="msg_body" id="msg_body" <?php if(($perm < 2 && $settings->msg_blocked_users==0) || ($thread->hidden_from==1 || $thread->hidden_to==1)) {?>disabled<?php } ?>/>
                  <?php /* textarea rows="10" cols="80"  id="mytextarea" name="msg_body"></textarea> */ ?></div>
                  <input type="hidden" name="csrf" value="<?=$csrf?>" />
                </p>
                <p>
                  <input type="submit" class="btn btn-primary" name="reply" value="Reply">
                </form>
              </div> <!-- /.col -->

              <?php if(($settings->msg_blocked_users==1 || ($perm==2 && $settings->msg_blocked_users==0)) && (!$thread->hidden_from==1 && !$thread->hidden_to==1)) {?>
                <div id="reply" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Reply</h4>
                      </div>
                      <div class="modal-body">
                        <form name="reply_form" action="message.php?id=<?=$id?>" method="post">
                          <div align="center">
                            <textarea rows="10" cols="80"  id="mytextarea" name="msg_body"></textarea></div>
                            <input type="hidden" name="csrf" value="<?=$csrf?>" />
                          </p>
                          <p>
                            <br />
                          </div>
                          <div class="modal-footer">
                            <div class="btn-group">       <input type="hidden" name="csrf" value="<?=$csrf?>" />
                              <input class='btn btn-primary' type='submit' name="reply" value='Reply' class='submit' /></div>
                            </form>
                            <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div><?php } ?>
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

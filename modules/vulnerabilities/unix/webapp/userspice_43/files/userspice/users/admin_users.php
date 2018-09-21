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

require_once 'init.php';
require_once $abs_us_root.$us_url_root.'users/includes/header.php';
require_once $abs_us_root.$us_url_root.'users/includes/navigation.php';
if (!securePage($_SERVER['PHP_SELF'])){die();}

//PHP Goes Here!
$errors = $successes = [];
$query = $db->query("SELECT * FROM email");
$results = $query->first();
$act = $results->email_act;
$form_valid=TRUE;
$permOpsQ = $db->query("SELECT * FROM permissions");
$permOps = $permOpsQ->results();
// dnd($permOps);
$validation = new Validate();
if (!empty($_POST)) {
  if(!empty($_POST['cloak'])){
    if(!in_array($user->data()->id,$master_account)){
      die("You do not have permission to do this! Shame on you!");
    }else{
      $to_user = Input::get('to_user');
      if($to_user == 1){
        logger($user->data()->id,"Cloaking","WARNING! Attempted to become user 1!!!");
        Redirect::to('admin_users.php?err=You+cannot+become+user+1');
      }elseif(!is_numeric($to_user)){
        Redirect::to('admin_users.php?err=The+user+id+must+be+numeric!');
      }elseif($to_user == $user->data()->id){
        Redirect::to('admin_users.php?err=Cloaking+into+yourself+would+open+up+a+black+hole!');
      }else{
        $check = $db->query("SELECT id FROM users WHERE id = ?",array($to_user));
        $count = $check->count();
        if($count < 1){
          Redirect::to('admin_users.php?err=User+not+found');
        }else{
          $_SESSION['cloak_from']=$user->data()->id;
          $_SESSION['cloak_to']=$to_user;
          logger($user->data()->id,"Cloaking","cloaked into ".$to_user);
          Redirect::to('account.php?err=You+are+now+cloaked!');
        }
      }
    }
  }





  //Manually Add User
  if(!empty($_POST['addUser'])) {
    $join_date = date("Y-m-d H:i:s");
    $fname = Input::get('fname');
    $lname = Input::get('lname');
    $email = Input::get('email');
    if($settings->auto_assign_un==1) {
      $preusername = $fname[0];
      $preusername .= $lname;
      $preQ = $db->query("SELECT username FROM users WHERE username = ?",array($preusername));
      $preQCount = $preQ->count();
      if($preQCount == 0)
      {
        $username = strtolower($preusername);
      }
      else
      {
        $preusername2 = $fname;
        $preusername2 .= $lname[0];
        $preQ2 = $db->query("SELECT username FROM users WHERE username = ?",array($preusername2));
        $preQCount2 = $preQ2->count();
        if($preQCount2 == 0)
        {
          $username = strtolower($preusername2);
        }
        else
        {
          $username = $email;
        }
      } }
      if($settings->auto_assign_un==0) $username = Input::get('username');
      $token = $_POST['csrf'];

      if(!Token::check($token)){
        include('../usersc/scripts/token_error.php');
      }

      $form_valid=FALSE; // assume the worst
      if($settings->auto_assign_un==0) {
        $validation->check($_POST,array(
          'username' => array(
            'display' => 'Username',
            'required' => true,
            'min' => $settings->min_un,
            'max' => $settings->max_un,
            'unique' => 'users',
          ),
          'fname' => array(
            'display' => 'First Name',
            'required' => true,
            'min' => 1,
            'max' => 60,
          ),
          'lname' => array(
            'display' => 'Last Name',
            'required' => true,
            'min' => 1,
            'max' => 60,
          ),
          'email' => array(
            'display' => 'Email',
            'required' => true,
            'valid_email' => true,
            'unique' => 'users',
          ),

          'password' => array(
            'display' => 'Password',
            'required' => true,
            'min' => $settings->min_pw,
            'max' => $settings->max_pw,
          ),
          'confirm' => array(
            'display' => 'Confirm Password',
            'required' => true,
            'matches' => 'password',
          ),
        )); }
        if($settings->auto_assign_un==1) {
          $validation->check($_POST,array(
            'fname' => array(
              'display' => 'First Name',
              'required' => true,
              'min' => 1,
              'max' => 60,
            ),
            'lname' => array(
              'display' => 'Last Name',
              'required' => true,
              'min' => 1,
              'max' => 60,
            ),
            'email' => array(
              'display' => 'Email',
              'required' => true,
              'valid_email' => true,
              'unique' => 'users',
            ),

            'password' => array(
              'display' => 'Password',
              'required' => true,
              'min' => $settings->min_pw,
              'max' => $settings->max_pw,
            ),
            'confirm' => array(
              'display' => 'Confirm Password',
              'required' => true,
              'matches' => 'password',
            ),
          ));
        }
        if($validation->passed()) {
          $form_valid=TRUE;
          try {
            // echo "Trying to create user";
            $fields=array(
              'username' => $username,
              'fname' => ucfirst(Input::get('fname')),
              'lname' => ucfirst(Input::get('lname')),
              'email' => Input::get('email'),
              'password' =>
              password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12)),
              'permissions' => 1,
              'account_owner' => 1,
              'join_date' => $join_date,
              'email_verified' => 1,
              'active' => 1,
              'vericode' => randomstring(15),
              'force_pr' => $settings->force_pr,
            );
            $db->insert('users',$fields);
            $theNewId=$db->lastId();
            // bold($theNewId);
            $perm = Input::get('perm');
            $addNewPermission = array('user_id' => $theNewId, 'permission_id' => 1);
            $db->insert('user_permission_matches',$addNewPermission);
            $db->insert('profiles',['user_id'=>$theNewId, 'bio'=>'']);
            include('../usersc/scripts/during_user_creation.php');
            if(isset($_POST['sendEmail'])) {
              $userDetails = fetchUserDetails(NULL, NULL, $theNewId);
              $params = array(
                'username' => $username,
                'password' => Input::get('password'),
                'sitename' => $settings->site_name,
                'force_pr' => $settings->force_pr,
                'fname' => Input::get('fname'),
                'email' => rawurlencode($userDetails->email),
                'vericode' => $userDetails->vericode,
              );
              $to = rawurlencode($email);
              $subject = 'Welcome to '.$settings->site_name;
              $body = email_body('_email_adminUser.php',$params);
              email($to,$subject,$body);
            }
            logger($user->data()->id,"User Manager","Added user $username.");
            Redirect::to('admin_user.php?id='.$theNewId);
          } catch (Exception $e) {
            die($e->getMessage());
          }

        }
      }
    }
    $userData = fetchAllUsers("permissions DESC,id"); //Fetch information for all users
    $random_password = random_password();
    ?>

    <div id="page-wrapper">
      <div class="container">
        <!-- Page Heading -->
        <div class="row">
          <div class="col-xs-12 col-md-6">
            <h1>Manage Users</h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <?=resultBlock($errors,$successes);?>
            <?php if(!$validation->errors()=='') {?><div class="alert alert-danger"><?=display_errors($validation->errors());?></div><?php } ?>
            <div class="row">
              <hr />
              <?php if(in_array($user->data()->id,$master_account)){ ?>
                <form class="" action="admin_users.php" method="post">
                  <label for="">Enter a User ID to turn into that user</label><br>
                  <input type="number" name="to_user" value="">
                  <input type="submit" name="cloak" value="Cloak!" class='btn btn-danger'>
                </form>
              <?php } ?>
              <a class="pull-right" href="#" data-toggle="modal" data-target="#adduser"><i class="glyphicon glyphicon-plus"></i> Manually Add User</a>
              <div class="row">
                <div class="col-xs-12">
                  <div class="alluinfo">&nbsp;</div>
                  <div class="allutable">
                    <table id="paginate" class='table table-hover table-list-search'>
                      <thead>
                        <tr>
                          <th></th><th></th><th>Username</th><th>Name</th><th>Email</th>
                          <th>Last Sign In</th><?php if($act==1) {?><th>Verified</th><?php } ?><th>Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        //Cycle through users
                        foreach ($userData as $v1) {
                          ?>
                          <tr>
                            <td><a class="nounderline" href='admin_user.php?id=<?=$v1->id?>'><?=$v1->id?></a></td>
                            <td><?php if($v1->force_pr==1) {?><font color="red"><i class="glyphicon glyphicon-lock"></i></font><?php } ?></td>
                            <td><a class="nounderline" href='admin_user.php?id=<?=$v1->id?>'><?=$v1->username?></a></td>
                            <td><?=$v1->fname?> <?=$v1->lname?></td>
                            <td><?=$v1->email?></td>
                            <td><?php if($v1->last_login != 0) { echo $v1->last_login; } else {?> <i>Never</i> <?php }?></td>
                            <?php if($act==1) {?><td>
                              <?php if($v1->email_verified == 1){
                                echo "<i class='glyphicon glyphicon-ok'></i>";
                              } ?>
                            </td><?php } ?>
                            <td><i class="fa fa-fw fa-<?php if($v1->permissions==1) {?>un<?php } ?>lock"></i></td>
                          </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div id="adduser" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">User Addition</h4>
                  </div>
                  <div class="modal-body">
                    <form class="form-signup" action="admin_users.php" method="POST" id="payment-form">
                      <div class="panel-body">
                        <?php if($settings->auto_assign_un==0) {?><label>Username: </label>&nbsp;&nbsp;<span id="usernameCheck" class="small"></span><input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php if (!$form_valid && !empty($_POST)){ echo $username;} ?>" required><?php } ?>
                          <label>First Name: </label><input type="text" class="form-control" id="fname" name="fname" placeholder="First Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $fname;} ?>" required>
                          <label>Last Name: </label><input type="text" class="form-control" id="lname" name="lname" placeholder="Last Name" value="<?php if (!$form_valid && !empty($_POST)){ echo $lname;} ?>" required>
                          <label>Email: </label><input  class="form-control" type="text" name="email" id="email" placeholder="Email Address" value="<?php if (!$form_valid && !empty($_POST)){ echo $email;} ?>" required >
                          <label>Password: </label>
                          <div class="input-group" data-container="body">
                            <span class="input-group-addon password_view_control" id="addon1"><span class="glyphicon glyphicon-eye-open"></span></span>
                            <input  class="form-control" type="password" name="password" id="password" <?php if($settings->force_pr==1) { ?>value="<?=$random_password?>" readonly<?php } ?> placeholder="Password" required aria-describedby="passwordhelp">
                            <?php if($settings->force_pr==1) { ?>
                              <span class="input-group-addon" id="addon2"><a class="nounderline pwpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="The Administrator has manual creation password resets enabled. If you choose to send an email to this user, it will supply them with the password reset link and let them know they have an account. If you choose to not, you should manually supply them with this password (discouraged).">Why can't I edit this?</a></span>
                            <?php } ?>
                          </div>
                          <label>Confirm Password: </label>
                          <div class="input-group" data-container="body">
                            <span class="input-group-addon password_view_control" id="addon1"><span class="glyphicon glyphicon-eye-open"></span></span>
                            <input  type="password" id="confirm" name="confirm" <?php if($settings->force_pr==1) { ?>value="<?=$random_password?>" readonly<?php } ?> class="form-control" placeholder="Confirm Password" required >
                            <?php if($settings->force_pr==1) { ?>
                              <span class="input-group-addon" id="addon2"><a class="nounderline pwpopover" data-container="body" data-toggle="popover" data-placement="top" data-content="The Administrator has manual creation password resets enabled. If you choose to send an email to this user, it will supply them with the password reset link and let them know they have an account. If you choose to not, you should manually supply them with this password (discouraged).">Why can't I edit this?</a></span>
                            <?php } ?>
                          </div>
                          <label><input type="checkbox" name="sendEmail" id="sendEmail" checked /> Send Email?</label>
                          <br />
                        </div>
                        <div class="modal-footer">
                          <div class="btn-group">
                            <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                            <input class='btn btn-primary' type='submit' id="addUser" name="addUser" value='Add User' class='submit' /></div>
                            <div class="btn-group"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>


        <!-- End of main content section -->

        <?php require_once $abs_us_root.$us_url_root.'users/includes/page_footer.php'; // the final html footer copyright row + the external js calls ?>

        <!-- Place any per-page javascript here -->
        <script src="js/pagination/jquery.dataTables.js" type="text/javascript"></script>
        <script src="js/pagination/dataTables.js" type="text/javascript"></script>
        <script src="js/jwerty.js"></script>
        <script>
        $(document).ready(function() {
          jwerty.key('esc', function(){
            $('.modal').modal('hide');
          });
          $('#paginate').DataTable({"pageLength": 25,"aLengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]], "aaSorting": []});

          $('.password_view_control').hover(function () {
            $('#password').attr('type', 'text');
            $('#confirm').attr('type', 'text');
          }, function () {
            $('#password').attr('type', 'password');
            $('#confirm').attr('type', 'password');
          });


          $('[data-toggle="popover"], .pwpopover').popover();
          $('.pwpopover').on('click', function (e) {
            $('.pwpopover').not(this).popover('hide');
          });
          $('.modal').on('hidden.bs.modal', function () {
            $('.pwpopover').popover('hide');
          });
        });
        </script>

        <?php if($settings->auto_assign_un==0) { ?>
          <script type="text/javascript">
          $(document).ready(function(){
            var x_timer;
            $("#username").keyup(function (e){
              clearTimeout(x_timer);
              var username = $(this).val();
              if (username.length > 0) {
                x_timer = setTimeout(function(){
                  check_username_ajax(username);
                }, 500);
              }
              else $('#usernameCheck').text('');
            });

            function check_username_ajax(username){
              $("#usernameCheck").html('Checking...');
              $.post('parsers/existingUsernameCheck.php', {'username': username}, function(response) {
                if (response == 'error') $('#usernameCheck').html('There was an error while checking the username.');
                else if (response == 'taken') { $('#usernameCheck').html('<i class="glyphicon glyphicon-remove" style="color: red; font-size: 12px"></i> This username is taken.');
                $('#addUser').prop('disabled', true); }
                else if (response == 'valid') { $('#usernameCheck').html('<i class="glyphicon glyphicon-ok" style="color: green; font-size: 12px"></i> This username is not taken.');
                $('#addUser').prop('disabled', false); }
                else { $('#usernameCheck').html('');
                $('#addUser').prop('disabled', false); }
              });
            }
          });
        </script>
      <?php } ?>

      <?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; // currently just the closing /body and /html ?>
